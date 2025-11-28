const mysql = require('mysql2');

// Create Connection
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',      // Leave empty for XAMPP
    database: 'hostelhub' // Ensure this matches your phpMyAdmin DB name
});

// Connect
db.connect((err) => {
    if (err) {
        console.error('❌ Database connection failed: ' + err.stack);
        return;
    }
    console.log('✅ Connected to MySQL Database.');
});

module.exports = db;