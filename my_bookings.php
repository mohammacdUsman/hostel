<?php
include 'db.php';

// 1. SECURITY CHECK (MUST BE BEFORE HEADER_SIDEBAR)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php"); 
    exit();
}

// 2. Load Layout (Only after security check passes)
include 'header_sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4" style="min-height: 60vh;">
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-journal-bookmark-fill fs-2 me-3 text-gold" style="color: var(--gold);"></i>
            <h2 class="fw-bold mb-0" style="font-family: 'Cinzel';">My Bookings & Payments</h2>
        </div>

        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Hostel Details</th>
                            <th>Booking Date</th>
                            <th>Status</th>
                            <th>Payment & Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sid = $_SESSION['user_id'];
                        $sql = "SELECT b.*, h.name, h.image, h.area, h.price 
                                FROM bookings b 
                                JOIN hostels h ON b.hostel_id = h.id 
                                WHERE b.student_id = $sid 
                                ORDER BY b.created_at DESC";
                        $res = $conn->query($sql);

                        if ($res && $res->num_rows > 0) {
                            while($row = $res->fetch_assoc()) {
                                $status = ucfirst($row['status']);
                                $badge = ($status=='Approved') ? 'bg-success' : (($status=='Pending') ? 'bg-warning text-dark' : 'bg-danger');
                                // Handle image fallback
                                $img = !empty($row['image']) ? 'uploads/'.$row['image'] : 'https://via.placeholder.com/50';

                                echo "<tr>
                                    <td class='ps-4'>
                                        <div class='d-flex align-items-center'>
                                            <img src='$img' class='rounded me-3' style='width:50px; height:50px; object-fit:cover;'>
                                            <div>
                                                <div class='fw-bold'>{$row['name']}</div>
                                                <small class='text-muted'>{$row['area']} | Rs. ".number_format($row['price'])."</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>".date('d M Y', strtotime($row['created_at']))."</td>
                                    <td><span class='badge $badge rounded-pill'>$status</span></td>
                                    <td>
                                        <div class='d-flex gap-2'>";

                                // REPORT ISSUE BUTTON
                                echo "<button class='btn btn-sm btn-light border' onclick='openComplaint({$row['hostel_id']})' title='Report Issue'>
                                        <i class='bi bi-exclamation-triangle text-danger'></i>
                                      </button>";

                                // PAYMENT LOGIC
                                if ($status == 'Approved') {
                                    if (empty($row['payment_proof'])) {
                                        echo "<button class='btn btn-sm btn-gold text-white fw-bold shadow-sm' onclick='openUploadModal({$row['id']})'>
                                                <i class='bi bi-upload'></i> Pay Now
                                              </button>";
                                    } else {
                                        echo "<a href='receipt.php?id={$row['id']}' target='_blank' class='btn btn-sm btn-outline-dark'>
                                                <i class='bi bi-receipt'></i> Receipt
                                              </a>";
                                    }
                                } else {
                                    echo "<span class='text-muted small'>-</span>";
                                }

                                echo "  </div>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-5 text-muted'>You haven't booked any hostels yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 1. COMPLAINT MODAL -->
<div class="modal fade" id="complaintModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="process_complaint.php" method="POST" class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-tools"></i> Report Issue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="hostel_id" id="comp_hid">
                <label class="fw-bold mb-2">What's the problem?</label>
                <select name="issue" class="form-select mb-3">
                    <option>Electricity Issue</option>
                    <option>WiFi Not Working</option>
                    <option>Plumbing / Water</option>
                    <option>Cleanliness</option>
                    <option>Other</option>
                </select>
                <label class="fw-bold mb-2">Description</label>
                <textarea name="desc" class="form-control" rows="3" placeholder="Describe the issue in detail..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger w-100">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. PAYMENT UPLOAD MODAL -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="upload_payment.php" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gold text-white" style="background: var(--gold);">
                <h5 class="modal-title fw-bold"><i class="bi bi-credit-card-2-front-fill"></i> Upload Payment Proof</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="alert alert-light border mb-3">
                    <small class="text-muted d-block mb-1">Please transfer rent to Owner's Account and upload the screenshot here.</small>
                    <strong>0300-1234567 (HostelHub Admin)</strong>
                </div>
                <input type="hidden" name="booking_id" id="pay_bid">
                <label class="fw-bold mb-2 d-block text-start">Upload Screenshot:</label>
                <input type="file" name="proof" class="form-control" required accept="image/*">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-gold text-white w-100">Submit Proof</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openComplaint(hid) {
        document.getElementById('comp_hid').value = hid;
        var modal = new bootstrap.Modal(document.getElementById('complaintModal'));
        modal.show();
    }

    function openUploadModal(bid) {
        document.getElementById('pay_bid').value = bid;
        var modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>