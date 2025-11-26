<?php 
include 'db.php'; 
include 'header_sidebar.php'; 

// HANDLE FORM SUBMISSION
if(isset($_POST['send_msg'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $msg = htmlspecialchars($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $msg);
    
    if($stmt->execute()){
        echo "<script>alert('Message Sent! We will contact you soon.'); window.location='contact.php';</script>";
    } else {
        echo "<script>alert('Error sending message.');</script>";
    }
}
?>

<div class="content-wrapper container mt-5">
    
    <div class="text-center mb-5">
        <h5 class="text-gold fw-bold" style="color: var(--gold);">GET IN TOUCH</h5>
        <h1 class="fw-bold" style="font-family: 'Cinzel';">Contact Us</h1>
        <p class="text-muted">Have questions? We are here to help.</p>
    </div>

    <div class="row g-5">
        
        <!-- LEFT: Contact Info -->
        <div class="col-lg-5">
            <div class="card bg-dark text-white p-4 border-0 shadow-lg h-100" style="border-radius: 15px;">
                <h3 class="fw-bold mb-4" style="font-family: 'Cinzel';">Contact Info</h3>
                
                <div class="d-flex mb-4">
                    <div class="me-3"><i class="bi bi-geo-alt-fill fs-4 text-gold" style="color: var(--gold);"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Our Location</h6>
                        <p class="small opacity-75">D-Ground, Peoples Colony, Faisalabad, Pakistan.</p>
                    </div>
                </div>

                <div class="d-flex mb-4">
                    <div class="me-3"><i class="bi bi-envelope-fill fs-4 text-gold" style="color: var(--gold);"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Email Address</h6>
                        <p class="small opacity-75">support@hostelhub.pk<br>info@hostelhub.pk</p>
                    </div>
                </div>

                <div class="d-flex mb-4">
                    <div class="me-3"><i class="bi bi-telephone-fill fs-4 text-gold" style="color: var(--gold);"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Phone Number</h6>
                        <p class="small opacity-75">+92 300 1234567<br>+92 321 7654321</p>
                    </div>
                </div>

                <hr class="border-secondary my-4">

                <h6 class="fw-bold">Follow Us</h6>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-white fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white fs-5"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-white fs-5"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
        </div>

        <!-- RIGHT: Form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-5 h-100">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-bold small mb-1">Your Name</label>
                            <input type="text" name="name" class="form-control bg-light border-0 p-3" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold small mb-1">Your Email</label>
                            <input type="email" name="email" class="form-control bg-light border-0 p-3" placeholder="name@example.com" required>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold small mb-1">Subject</label>
                            <input type="text" name="subject" class="form-control bg-light border-0 p-3" placeholder="Inquiry about..." required>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold small mb-1">Message</label>
                            <textarea name="message" class="form-control bg-light border-0 p-3" rows="5" placeholder="Write your message here..." required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="send_msg" class="btn btn-gold w-100 py-3 fw-bold shadow-sm">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- MAP SECTION -->
    <div class="mt-5 mb-5">
        <div class="ratio ratio-21x9 rounded shadow-sm border overflow-hidden">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3404.7947925577423!2d73.0852697151036!3d31.419879981402965!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x392242a1d8422977%3A0x387f22a0c9227739!2sD%20Ground%20Faisalabad!5e0!3m2!1sen!2s!4v1623456789012!5m2!1sen!2s" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>