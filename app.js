const fs = require('fs'); // Required to delete files
const express = require('express');
const session = require('express-session');
const bodyParser = require('body-parser');
const path = require('path');
const bcrypt = require('bcrypt');
const multer = require('multer');
const mysql = require('mysql2');
const axios = require('axios'); // Required for AI
const db = require('./db'); 

const app = express();

// --- CONFIGURATION ---
app.set('view engine', 'ejs'); 
app.use(express.static(path.join(__dirname, 'public'))); 
app.use('/uploads', express.static(path.join(__dirname, 'public/uploads')));
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

app.use(session({
    secret: 'hostelhub_secret_key',
    resave: false,
    saveUninitialized: true
}));

// Global Variable Middleware (User & Unread Messages)
app.use((req, res, next) => {
    res.locals.user = req.session.user || null;
    
    // If user is logged in, count unread messages
    if (req.session.user) {
        const uid = req.session.user.id;
        db.query("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0", [uid], (err, result) => {
            // Make 'unreadCount' available in all EJS files
            res.locals.unreadCount = result ? result[0].count : 0;
            next();
        });
    } else {
        res.locals.unreadCount = 0;
        next();
    }
});

const storage = multer.diskStorage({
    destination: './public/uploads/',
    filename: function(req, file, cb){ cb(null, Date.now() + '_' + file.originalname); }
});
const upload = multer({ storage: storage });

// ================= ROUTES =================

// 1. HOME PAGE (With Advanced Filters)
app.get('/', (req, res) => {
    let sql = "SELECT * FROM hostels WHERE 1=1"; // 1=1 allows appending AND conditions
    let params = [];

    // Filter by Area
    if (req.query.area && req.query.area !== '') {
        sql += " AND area LIKE ?";
        params.push('%' + req.query.area + '%');
    }

    // Filter by Category (Boys/Girls)
    if (req.query.category && req.query.category !== '') {
        sql += " AND category = ?";
        params.push(req.query.category);
    }

    // Filter by Max Price
    if (req.query.price && req.query.price !== '') {
        sql += " AND price <= ?";
        params.push(req.query.price);
    }

    db.query(sql, params, (err, results) => {
        if (err) throw err;
        res.render('index', { hostels: results, query: req.query });
    });
});

// 2. STATIC PAGES
app.get('/about', (req, res) => res.render('about'));
app.get('/contact', (req, res) => res.render('contact'));
app.post('/contact', (req, res) => {
    const { name, email, subject, message } = req.body;
    db.query("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)", 
    [name, email, subject, message], (err) => { res.redirect('/contact'); });
});

// 3. AUTHENTICATION
app.get('/login', (req, res) => res.render('login', { error: null }));
app.post('/login', (req, res) => {
    const { email, password } = req.body;
    db.query('SELECT * FROM users WHERE email = ?', [email], async (err, results) => {
        if (results.length === 0) return res.render('login', { error: "Email not found" });
        const user = results[0];
        const isMatch = await bcrypt.compare(password, user.password);
        if(isMatch) {
            req.session.user = user;
            if(user.role === 'owner') return res.redirect('/owner_dashboard');
            res.redirect('/');
        } else {
            res.render('login', { error: "Incorrect Password" });
        }
    });
});

app.get('/register', (req, res) => res.render('register', { error: null }));
app.post('/register', async (req, res) => {
    const { name, email, password, confirm_password, role } = req.body;
    if(password !== confirm_password) return res.render('register', { error: "Passwords do not match" });
    
    db.query('SELECT id FROM users WHERE email = ?', [email], async (err, result) => {
        if(result.length > 0) return res.render('register', { error: "Email exists" });
        const hashedPassword = await bcrypt.hash(password, 10);
        db.query('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)', [name, email, hashedPassword, role], (err) => {
            res.redirect('/login');
        });
    });
});
app.get('/logout', (req, res) => { req.session.destroy(() => res.redirect('/login')); });

// 3. OWNER DASHBOARD
app.get('/owner_dashboard', (req, res) => {
    if (!req.session.user || req.session.user.role !== 'owner') return res.redirect('/login');
    const ownerId = req.session.user.id;

    // 1. Get Hostels
    db.query('SELECT * FROM hostels WHERE owner_id = ?', [ownerId], (err, hostels) => {
        
        // 2. Get Bookings with Payment Proof
        const sqlBookings = `SELECT b.id as booking_id, b.message, b.status, b.payment_proof, b.created_at, 
                             u.name as student_name, u.email, h.name as hostel_name 
                             FROM bookings b JOIN hostels h ON b.hostel_id = h.id JOIN users u ON b.student_id = u.id 
                             WHERE h.owner_id = ? ORDER BY b.created_at DESC`;

        db.query(sqlBookings, [ownerId], (err, bookings) => {
            
            // 3. CALCULATE STATS
            let totalViews = 0;
            hostels.forEach(h => totalViews += h.views);

            let pending = 0, approved = 0, rejected = 0;
            bookings.forEach(b => {
                if(b.status === 'Pending') pending++;
                if(b.status === 'Approved') approved++;
                if(b.status === 'Rejected') rejected++;
            });

            const stats = { totalViews, totalRequests: bookings.length, pending, approved, rejected };

            res.render('owner_dashboard', { my_hostels: hostels, bookings: bookings, stats: stats });
        });
    });
});
// 5. HOSTEL MANAGEMENT (Add/Edit/Delete)
app.get('/add_hostel', (req, res) => { 
    if (!req.session.user || req.session.user.role !== 'owner') return res.redirect('/login');
    res.render('add_hostel'); 
});
app.post('/add_hostel', upload.array('images', 5), (req, res) => {
    const { name, category, area, price, desc, map_embed, facilities } = req.body;
    const facilitiesStr = Array.isArray(facilities) ? facilities.join(',') : (facilities || '');
    const sql = "INSERT INTO hostels (owner_id, name, category, area, price, description, facilities, map_embed) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    db.query(sql, [req.session.user.id, name, category, area, price, desc, facilitiesStr, map_embed], (err, result) => {
        const hostelId = result.insertId;
        if (req.files && req.files.length > 0) {
            const imageValues = req.files.map(file => [hostelId, file.filename]);
            db.query("INSERT INTO hostel_images (hostel_id, image_path) VALUES ?", [imageValues]);
            db.query("UPDATE hostels SET image = ? WHERE id = ?", [req.files[0].filename, hostelId]);
        }
        res.redirect('/owner_dashboard');
    });
});

app.get('/edit_hostel/:id', (req, res) => {
    if (!req.session.user || req.session.user.role !== 'owner') return res.redirect('/login');
    db.query("SELECT * FROM hostels WHERE id = ?", [req.params.id], (err, result) => {
        db.query("SELECT * FROM hostel_images WHERE hostel_id = ?", [req.params.id], (err, images) => {
            res.render('edit_hostel', { hostel: result[0], images: images });
        });
    });
});
app.post('/edit_hostel/:id', upload.array('images', 5), (req, res) => {
    const { name, category, area, price, desc, map_embed, facilities } = req.body;
    const facilitiesStr = Array.isArray(facilities) ? facilities.join(',') : (facilities || '');
    db.query("UPDATE hostels SET name=?, category=?, area=?, price=?, description=?, facilities=?, map_embed=? WHERE id=?", 
    [name, category, area, price, desc, facilitiesStr, map_embed, req.params.id], () => {
        if (req.files && req.files.length > 0) {
            const imageValues = req.files.map(file => [req.params.id, file.filename]);
            db.query("INSERT INTO hostel_images (hostel_id, image_path) VALUES ?", [imageValues]);
            db.query("UPDATE hostels SET image = ? WHERE id = ?", [req.files[0].filename, req.params.id]);
        }
        res.redirect('/owner_dashboard');
    });
});
// DELETE IMAGE ROUTE (Fixed & deletes actual file)
app.get('/delete_image/:id', (req, res) => {
    if (!req.session.user || req.session.user.role !== 'owner') {
        return res.redirect('/login');
    }

    const imageId = req.params.id;
    const hostelId = req.query.hostel_id; // Get ID from the link we updated

    // 1. Get Image Name to delete file
    db.query("SELECT image_path FROM hostel_images WHERE id = ?", [imageId], (err, result) => {
        if (err) console.log(err);

        if (result.length > 0) {
            const filename = result[0].image_path;
            const filePath = path.join(__dirname, 'public/uploads', filename);

            // 2. Delete File from Folder
            if (fs.existsSync(filePath)) {
                fs.unlinkSync(filePath);
            }
        }

        // 3. Delete from Database
        db.query("DELETE FROM hostel_images WHERE id = ?", [imageId], () => {
            // 4. Redirect DIRECTLY to the edit page (Breaks the loop)
            if (hostelId) {
                res.redirect('/edit_hostel/' + hostelId);
            } else {
                res.redirect('/owner_dashboard');
            }
        });
    });
});

app.get('/update_booking/:id/:status', (req, res) => {
    if (!req.session.user || req.session.user.role !== 'owner') return res.redirect('/login');
    db.query('UPDATE bookings SET status = ? WHERE id = ?', [req.params.status, req.params.id], () => res.redirect('/owner_dashboard'));
});
// DELETE IMAGE ROUTE (Fixed)
app.get('/delete_image/:id', (req, res) => {
    if (!req.session.user || req.session.user.role !== 'owner') {
        return res.redirect('/login');
    }

    const imageId = req.params.id;

    // 1. Get the Hostel ID and Image Path before deleting
    db.query("SELECT hostel_id, image_path FROM hostel_images WHERE id = ?", [imageId], (err, result) => {
        if (err || result.length === 0) {
            return res.redirect('/owner_dashboard'); // Safety fallback
        }

        const hostelId = result[0].hostel_id;
        const imagePath = result[0].image_path;

        // 2. Delete from Database
        db.query("DELETE FROM hostel_images WHERE id = ?", [imageId], (err) => {
            if (err) console.error(err);

            // 3. Explicitly redirect back to the Edit Page for THIS hostel
            // This prevents the infinite loop
            res.redirect('/edit_hostel/' + hostelId);
        });
    });
});
// 6. MESS & ANNOUNCEMENTS
app.get('/manage_mess/:id', (req, res) => {
    if (!req.session.user || req.session.user.role !== 'owner') return res.redirect('/login');
    db.query("SELECT * FROM mess_menu WHERE hostel_id = ?", [req.params.id], (err, menu) => {
        res.render('manage_mess', { menu: menu, hostel_id: req.params.id });
    });
});
app.post('/manage_mess', (req, res) => {
    const { hostel_id, day, bkf, lun, din } = req.body;
    db.query("DELETE FROM mess_menu WHERE hostel_id = ?", [hostel_id], () => {
        for(let i=0; i<7; i++) {
            db.query("INSERT INTO mess_menu (hostel_id, day_name, breakfast, lunch, dinner) VALUES (?, ?, ?, ?, ?)", [hostel_id, day[i], bkf[i], lun[i], din[i]]);
        }
        res.redirect('/owner_dashboard');
    });
});

app.get('/manage_hostel/:id', (req, res) => {
    if (!req.session.user || req.session.user.role !== 'owner') return res.redirect('/login');
    db.query("SELECT announcement_text FROM hostels WHERE id = ?", [req.params.id], (err, result) => {
        res.render('manage_hostel', { announcement: result[0].announcement_text, hostel_id: req.params.id });
    });
});
app.post('/manage_hostel', (req, res) => {
    db.query("UPDATE hostels SET announcement_text = ? WHERE id = ?", [req.body.text, req.body.hostel_id], () => res.redirect('/owner_dashboard'));
});

// 7. STUDENT FEATURES (FIXED)
app.get('/details/:id', (req, res) => {
    const id = req.params.id;

    // 1. Update View Count
    db.query("UPDATE hostels SET views = views + 1 WHERE id = ?", [id]);

    // 2. Get Hostel
    db.query('SELECT * FROM hostels WHERE id = ?', [id], (err, result) => {
        if (err || result.length === 0) return res.send("Hostel Not Found");
        const hostel = result[0];

        // 3. Get Images
        db.query('SELECT image_path FROM hostel_images WHERE hostel_id = ?', [id], (err, images) => {
            
            // 4. Get Mess Menu
            db.query('SELECT * FROM mess_menu WHERE hostel_id = ?', [id], (err, mess) => {
                
                // 5. Get Reviews (List)
                db.query('SELECT r.*, u.name, u.profile_pic FROM reviews r JOIN users u ON r.student_id = u.id WHERE hostel_id = ? ORDER BY r.created_at DESC', [id], (err, reviews) => {
                    
                    // 6. Get Rating Stats (Avg & Count)
                    db.query('SELECT AVG(rating) as avg, COUNT(*) as count FROM reviews WHERE hostel_id = ?', [id], (err, ratingRes) => {
                        
                        // --- SAFETY FIX HERE ---
                        const ratingData = (ratingRes && ratingRes.length > 0) ? ratingRes[0] : { avg: 0, count: 0 };

                        res.render('details', { 
                            hostel: hostel, 
                            images: images, 
                            mess: mess, 
                            reviews: reviews,
                            rating: ratingData, // Use safe variable
                            is_owner: (req.session.user && req.session.user.id === hostel.owner_id)
                        });
                    });
                });
            });
        });
    });
});
app.post('/book_hostel', (req, res) => {
    // Prevent crash if user not logged in
    if (!req.session.user || req.session.user.role !== 'student') {
        return res.redirect('/login');
    }
    
    const { hostel_id } = req.body;
    const studentId = req.session.user.id;
    const message = "I am interested in this hostel.";

    // Validate Data
    if (!hostel_id) {
        console.error("Error: Missing Hostel ID");
        return res.redirect('/');
    }

    db.query('INSERT INTO bookings (hostel_id, student_id, message) VALUES (?, ?, ?)', 
    [hostel_id, studentId, message], (err) => {
        if (err) {
            console.error("Database Error on Booking:", err); // Log error instead of crashing
            return res.send("Error processing booking. Please try again.");
        }
        res.redirect('/my_bookings');
    });
});

// 8. PROFILE & WISHLIST
app.get('/profile', (req, res) => {
    if (!req.session.user) return res.redirect('/login');
    db.query("SELECT * FROM users WHERE id = ?", [req.session.user.id], (err, result) => res.render('profile', { user: result[0], msg: null }));
});
app.post('/update_profile', upload.single('profile_pic'), (req, res) => {
    if (!req.session.user) return res.redirect('/login');
    const { name, phone, about } = req.body;
    let sql = "UPDATE users SET name=?, phone=?, about=? WHERE id=?";
    let params = [name, phone, about, req.session.user.id];
    if (req.file) {
        sql = "UPDATE users SET name=?, phone=?, about=?, profile_pic=? WHERE id=?";
        params = [name, phone, about, req.file.filename, req.session.user.id];
    }
    db.query(sql, params, () => res.redirect('/profile'));
});

app.get('/my_wishlist', (req, res) => {
    if (!req.session.user) return res.redirect('/login');
    const sql = "SELECT h.* FROM wishlist w JOIN hostels h ON w.hostel_id = h.id WHERE w.student_id = ?";
    db.query(sql, [req.session.user.id], (err, result) => res.render('my_wishlist', { hostels: result }));
});
app.post('/toggle_wishlist', (req, res) => {
    if (!req.session.user) return res.send('login_required');
    const { hostel_id } = req.body;
    const uid = req.session.user.id;
    db.query("SELECT id FROM wishlist WHERE student_id = ? AND hostel_id = ?", [uid, hostel_id], (err, result) => {
        if (result.length > 0) {
            db.query("DELETE FROM wishlist WHERE student_id = ? AND hostel_id = ?", [uid, hostel_id], () => res.send('removed'));
        } else {
            db.query("INSERT INTO wishlist (student_id, hostel_id) VALUES (?, ?)", [uid, hostel_id], () => res.send('added'));
        }
    });
});

// 9. ADVANCED (Compare, Payment, Chat, AI)
app.get('/compare', (req, res) => {
    const { h1, h2 } = req.query;
    if(!h1 || !h2) return res.redirect('/');
    db.query("SELECT * FROM hostels WHERE id IN (?, ?)", [h1, h2], (err, results) => {
        if(err || results.length < 2) return res.redirect('/');
        const h1Data = results[0];
        const h2Data = results[1];
        const priceWinner = h1Data.price < h2Data.price ? h1Data.id : h2Data.id;
        const facWinner = (h1Data.facilities?.split(',').length || 0) > (h2Data.facilities?.split(',').length || 0) ? h1Data.id : h2Data.id;
        res.render('compare', { h1: h1Data, h2: h2Data, priceWinner, facWinner });
    });
});

app.post('/upload_payment', upload.single('proof'), (req, res) => {
    if (!req.session.user) return res.redirect('/login');
    db.query("UPDATE bookings SET payment_proof = ? WHERE id = ?", [req.file.filename, req.body.booking_id], () => res.redirect('/my_bookings'));
});

app.get('/receipt/:id', (req, res) => {
    if (!req.session.user) return res.redirect('/login');
    const sql = `SELECT b.*, h.name as hostel_name, h.price, u.name as student_name, u.email FROM bookings b JOIN hostels h ON b.hostel_id = h.id JOIN users u ON b.student_id = u.id WHERE b.id = ?`;
    db.query(sql, [req.params.id], (err, result) => res.render('receipt', { data: result[0] }));
});

// Community Chat
app.get('/community', (req, res) => res.render('community'));
app.get('/area_chat', (req, res) => res.render('area_chat', { area: req.query.area || 'General' }));
app.post('/api_community', (req, res) => {
    const { action, area, message } = req.body;
    if(action === 'send') {
        db.query("INSERT INTO community_messages (area_name, sender_id, message) VALUES (?, ?, ?)", [area, req.session.user.id, message], () => res.send('sent'));
    } else if(action === 'fetch') {
        const sql = `SELECT m.*, u.name, u.profile_pic FROM community_messages m JOIN users u ON m.sender_id = u.id WHERE m.area_name = ? ORDER BY m.created_at ASC`;
        db.query(sql, [area], (err, msgs) => {
            let html = "";
            msgs.forEach(msg => {
                const isMe = (req.session.user && msg.sender_id === req.session.user.id);
                const cls = isMe ? 'my-msg' : 'other-msg';
                const pic = msg.profile_pic ? '/uploads/'+msg.profile_pic : 'https://via.placeholder.com/30';
                html += `<div class='d-flex ${isMe ? "justify-content-end" : ""} mb-2'>${!isMe ? `<img src='${pic}' class='rounded-circle me-2' style='width:30px; height:30px; object-fit:cover;'>` : ""}<div class='msg ${cls}'>${!isMe ? `<span class='sender-name'>${msg.name}</span>` : ""}${msg.message}</div></div>`;
            });
            res.send(html);
        });
    }
});

// AI Chatbot
app.post('/api_ai', async (req, res) => {
    const userMessage = req.body.message;
    db.query("SELECT name, area, price, facilities FROM hostels LIMIT 5", async (err, hostels) => {
        let context = "You are the AI Assistant for HostelHub.pk. Here is the hostel data:\n";
        if(hostels.length > 0) { hostels.forEach(h => { context += `- ${h.name} in ${h.area}, Rs. ${h.price}, Features: ${h.facilities}\n`; }); }
        context += `\nUser asked: "${userMessage}". Answer briefly.`;

        const apiKey = "YOUR_GEMINI_API_KEY"; // <--- PASTE API KEY HERE
        const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${apiKey}`;
        try {
            const response = await axios.post(url, { contents: [{ parts: [{ text: context }] }] });
            res.send(response.data.candidates[0].content.parts[0].text);
        } catch (error) { res.send("I am overloaded. Try later."); }
    });
});
// --- CHAT ROUTES ---

// 1. Chat List (Inbox)
app.get('/chat_list', (req, res) => {
    if (!req.session.user) return res.redirect('/login');
    const myId = req.session.user.id;

    // Query to find people I have chatted with
    const sql = `
        SELECT DISTINCT u.id, u.name, u.role, u.profile_pic 
        FROM messages m 
        JOIN users u ON (m.sender_id = u.id OR m.receiver_id = u.id)
        WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
        ORDER BY m.created_at DESC`;

    db.query(sql, [myId, myId, myId], (err, users) => {
        res.render('chat_list', { chats: users });
    });
});

// 2. Chat Room
app.get('/chat/:id', (req, res) => {
    if (!req.session.user) return res.redirect('/login');
    
    const myId = req.session.user.id;
    const otherId = req.params.id;

    // Prevent crash if IDs are missing
    if (!otherId) return res.redirect('/chat_list');

    // Mark messages as read (Handle error silently)
    db.query("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?", [otherId, myId], (err) => {
        if(err) console.error("Error updating read status:", err);
    });

    // Get Receiver Info
    db.query("SELECT * FROM users WHERE id = ?", [otherId], (err, result) => {
        if (err) {
            console.error("DB Error fetching chat user:", err);
            return res.redirect('/chat_list');
        }
        if (result.length === 0) return res.redirect('/chat_list');
        
        res.render('chat', { receiver: result[0] });
    });
});

// 3. API to Fetch/Send Messages
app.post('/api_chat', (req, res) => {
    const { action, receiver_id, message } = req.body;
    const myId = req.session.user.id;

    if(action === 'send') {
        db.query("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)", 
        [myId, receiver_id, message], () => res.send('sent'));
    } 
    else if(action === 'fetch') {
        const sql = `SELECT * FROM messages 
                     WHERE (sender_id = ? AND receiver_id = ?) 
                        OR (sender_id = ? AND receiver_id = ?) 
                     ORDER BY created_at ASC`;
        db.query(sql, [myId, receiver_id, receiver_id, myId], (err, msgs) => {
            let html = "";
            msgs.forEach(msg => {
                const isMe = (msg.sender_id === myId);
                const cls = isMe ? 'sent' : 'received';
                html += `<div class='msg ${cls}'>${msg.message}</div>`;
            });
            res.send(html);
        });
    }
});
// --- SUBMIT REVIEW ROUTE ---
app.post('/submit_review', (req, res) => {
    // 1. Check if user is logged in
    if (!req.session.user) return res.redirect('/login');
    
    const { hostel_id, rating, comment } = req.body;
    const studentId = req.session.user.id;

    // 2. Check if already reviewed (Optional Prevention)
    db.query("SELECT id FROM reviews WHERE student_id = ? AND hostel_id = ?", [studentId, hostel_id], (err, result) => {
        if (result.length > 0) {
            // If reviewed, just go back
            return res.redirect('/details/' + hostel_id); 
        }

        // 3. Save Review
        const sql = "INSERT INTO reviews (hostel_id, student_id, rating, comment) VALUES (?, ?, ?, ?)";
        db.query(sql, [hostel_id, studentId, rating, comment], (err) => {
            if (err) console.log(err);
            // 4. Redirect back to details page
            res.redirect('/details/' + hostel_id);
        });
    });
});
// --- GET MY BOOKINGS ROUTE ---
// --- UPDATED: GET MY BOOKINGS (With Payment Details) ---
app.get('/my_bookings', (req, res) => {
    // 1. Security Check
    if (!req.session.user || req.session.user.role !== 'student') {
        return res.redirect('/login');
    }

    const studentId = req.session.user.id;

    // 2. Complex Query: Fetches Booking + Hostel + Owner's Payment Details
    // We use LEFT JOIN on payment_settings so we get the owner's bank info associated with that hostel
    const sql = `
        SELECT b.*, h.name, h.image, h.area, h.price,
               p.jazzcash_name, p.jazzcash_no, 
               p.easypaisa_name, p.easypaisa_no, 
               p.bank_name, p.bank_acc_title, p.bank_iban
        FROM bookings b 
        JOIN hostels h ON b.hostel_id = h.id 
        LEFT JOIN payment_settings p ON h.owner_id = p.owner_id 
        WHERE b.student_id = ? 
        ORDER BY b.created_at DESC`;

    db.query(sql, [studentId], (err, bookings) => {
        if (err) {
            console.error(err);
            return res.send("Database Error");
        }
        res.render('my_bookings', { bookings: bookings });
    });
});
// --- FEATURE: PAYMENT & RECEIPTS ---

// 1. Upload Payment Proof (Student)
app.post('/upload_payment', upload.single('proof'), (req, res) => {
    if (!req.session.user) return res.redirect('/login');
    
    const bookingId = req.body.booking_id;
    
    if (req.file) {
        const filename = req.file.filename;
        db.query("UPDATE bookings SET payment_proof = ? WHERE id = ?", [filename, bookingId], (err) => {
            if(err) console.log(err);
            res.redirect('/my_bookings');
        });
    } else {
        res.redirect('/my_bookings');
    }
});

// 2. Generate Digital Receipt (Viewable by Student & Owner)
app.get('/receipt/:id', (req, res) => {
    if (!req.session.user) return res.redirect('/login');
    const bookingId = req.params.id;

    const sql = `
        SELECT b.*, h.name as hostel_name, h.price, h.area,
               u.name as student_name, u.email, u.phone
        FROM bookings b 
        JOIN hostels h ON b.hostel_id = h.id 
        JOIN users u ON b.student_id = u.id 
        WHERE b.id = ?`;

    db.query(sql, [bookingId], (err, result) => {
        if(err || result.length === 0) return res.send("Receipt not found");
        res.render('receipt', { data: result[0] });
    });
});
// --- PAYMENT PROOF UPLOAD (Student) ---
app.post('/upload_payment', upload.single('proof'), (req, res) => {
    if (!req.session.user) return res.redirect('/login');
    
    const bookingId = req.body.booking_id;
    
    // Check if file was actually uploaded
    if (req.file) {
        const filename = req.file.filename;
        
        // Save filename to database
        db.query("UPDATE bookings SET payment_proof = ? WHERE id = ?", [filename, bookingId], (err) => {
            if(err) console.log("DB Error:", err);
            res.redirect('/my_bookings');
        });
    } else {
        // No file selected
        console.log("No file uploaded");
        res.redirect('/my_bookings');
    }
});
// --- FEATURE: PAYMENT SETTINGS (OWNER) ---

// 1. Show Payment Settings Form
app.get('/payment_settings', (req, res) => {
    if (!req.session.user || req.session.user.role !== 'owner') return res.redirect('/login');
    
    db.query("SELECT * FROM payment_settings WHERE owner_id = ?", [req.session.user.id], (err, result) => {
        // Pass existing data if available, else empty object
        res.render('payment_settings', { data: result[0] || {} });
    });
});

// 2. Save/Update Settings
app.post('/save_payment_settings', (req, res) => {
    if (!req.session.user || req.session.user.role !== 'owner') return res.redirect('/login');

    const uid = req.session.user.id;
    const { jc_name, jc_no, ep_name, ep_no, bank_name, bank_title, bank_iban } = req.body;

    // Check if exists
    db.query("SELECT id FROM payment_settings WHERE owner_id = ?", [uid], (err, result) => {
        if(result.length > 0) {
            // Update
            const sql = "UPDATE payment_settings SET jazzcash_name=?, jazzcash_no=?, easypaisa_name=?, easypaisa_no=?, bank_name=?, bank_acc_title=?, bank_iban=? WHERE owner_id=?";
            db.query(sql, [jc_name, jc_no, ep_name, ep_no, bank_name, bank_title, bank_iban, uid], () => res.redirect('/owner_dashboard?msg=updated'));
        } else {
            // Insert
            const sql = "INSERT INTO payment_settings (owner_id, jazzcash_name, jazzcash_no, easypaisa_name, easypaisa_no, bank_name, bank_acc_title, bank_iban) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            db.query(sql, [uid, jc_name, jc_no, ep_name, ep_no, bank_name, bank_title, bank_iban], () => res.redirect('/owner_dashboard?msg=saved'));
        }
    });
});
app.listen(3000, () => console.log('🚀 Server running on http://localhost:3000'));