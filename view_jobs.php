<?php
include 'db.php';
date_default_timezone_set('Asia/Colombo');
$current_time = date('Y-m-d\TH:i');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $order_number = $_POST['order_number'];
    $company_name = $_POST['company_name'];
    $contact_number = $_POST['contact_number'];
    $job_start_date = $_POST['job_start_date'];
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];
    $category = $_POST['category'];  // New field
    $quantity = $_POST['quantity'];  // New field

    $job_start_date = str_replace("T", " ", $job_start_date);
    $deadline = str_replace("T", " ", $deadline);

    $check_sql = "SELECT COUNT(*) FROM job_status WHERE order_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $order_number);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo '<div class="alert alert-danger mt-3">Error: Order number already exists. Please enter a unique order number.</div>';
    } else {
        $sql = "INSERT INTO job_status (date, order_number, company_name, contact_number, job_start_date, deadline, status, category, quantity)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssss', $date, $order_number, $company_name, $contact_number, $job_start_date, $deadline, $status, $category, $quantity);

        if ($stmt->execute()) {
            header('Location: home.php');
            exit();
        } else {
            echo '<div class="alert alert-danger mt-3">Error: ' . $stmt->error . '</div>';
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-label {
            font-weight: bold;
        }

        .form-control {
            width: 100%;
        }
    </style>
</head>

<body>

    <?php include('header.php'); ?>
    <div class="container mt-5">
        <h1 class="mb-4">Add Job</h1>
        <form method="POST" onsubmit="return validateForm()">
    <div class="row mb-3">

        <div class="col-md-6">
            <label for="date" class="form-label">Date and Time</label>
            <input type="datetime-local" name="date" id="date" class="form-control"
                value="<?php echo $current_time; ?>" min="<?php echo $current_time; ?>" required readonly>
        </div>

        <div class="col-md-6">
            <label for="order_number" class="form-label">Order Number</label>
            <input type="text" name="order_number" id="order_number" class="form-control" required>
        </div>
    </div>

    <div class="row mb-3">

        <div class="col-md-6">
            <label for="company_name" class="form-label">Company Name</label>
            <input type="text" name="company_name" id="company_name" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label for="contact_number" class="form-label">Contact Number</label>
            <input type="text" name="contact_number" id="contact_number" class="form-control" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="category" class="form-label">Category</label>
            <input type="text" name="category" id="category" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="0" required>
        </div>
    </div>

    <div class="row mb-3">

        <div class="col-md-6">
            <label for="job_start_date" class="form-label">Job Start Date and Time</label>
            <input type="datetime-local" name="job_start_date" id="job_start_date" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label for="deadline" class="form-label">Deadline Date and Time</label>
            <input type="datetime-local" name="deadline" id="deadline" class="form-control" required>
        </div>
    </div>

    <div class="row mb-3">

        <div class="col-md-6">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="Design">Design</option>
                <option value="Confirmation">Confirmation</option>
                <option value="Print">Print</option>
                <option value="Delivery">Delivery</option>
                <option value="Finished">Finished</option>
            </select>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
</form>

<!-- Custom Modal for Warning Messages -->
<div id="warning-modal" class="modal" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border: 1px solid #f5c6cb; background-color: #f8d7da;">
            <div class="modal-header" style="background-color: #f5c6cb; border-bottom: 1px solid #f1b0b7;">
                <h5 class="modal-title" style="color: #721c24;">Warning...!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="warning-text" style="color: #721c24;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function validateForm() {
    // Clear previous warning messages
    closeModal();

    // Validate Contact Number
    const contactNumber = document.getElementById('contact_number').value;
    const contactPattern = /^[0-9]{10}$/;  // Only allow exactly 10 digits
    if (!contactPattern.test(contactNumber)) {
        showWarning('Please enter a valid 10-digit phone number.');
        return false;
    }

    // Validate Quantity
    const quantity = document.getElementById('quantity').value;
    if (quantity < 0) {
        showWarning('Quantity cannot be negative.');
        return false;
    }

    // Validate Job Start Date and Deadline
    const jobStartDate = new Date(document.getElementById('job_start_date').value);
    const deadline = new Date(document.getElementById('deadline').value);
    if (deadline < jobStartDate) {
        showWarning('Deadline cannot be earlier than the job start date.');
        return false;
    }

    return true;  // All validations passed
}

function showWarning(message) {
    // Display the custom warning modal with the warning message
    document.getElementById('warning-text').textContent = message;
    document.getElementById('warning-modal').style.display = 'block';
}

function closeModal() {
    // Close the modal
    document.getElementById('warning-modal').style.display = 'none';
}
</script>

<style>
/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
}

.modal-dialog {
    width: 400px;
    margin-top: 300px;
}

.modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
}

.modal-header {
    padding-bottom: 10px;
}

.modal-title {
    margin: 0;
    font-size: 18px;
}

.close {
    background: none;
    border: none;
    font-size: 20px;
    color: #000;
}

.modal-footer {
    text-align: right;
}

button {
    cursor: pointer;
}
</style>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>
