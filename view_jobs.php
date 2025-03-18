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

    $job_start_date = str_replace("T", " ", $job_start_date);
    $deadline = str_replace("T", " ", $deadline);

    $check_sql = "SELECT COUNT(*) FROM JobStatus WHERE order_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $order_number);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo '<div class="alert alert-danger mt-3">Error: Order number already exists. Please enter a unique order number.</div>';
    } else {

        if ($result) {
            $result->free();
        }


        $sql = "INSERT INTO JobStatus (date, order_number, company_name, contact_number, job_start_date, deadline, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssss', $date, $order_number, $company_name, $contact_number, $job_start_date, $deadline, $status);

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
        <form method="POST">
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
                    <input type="text" name="contact_number" id="contact_number" class="form-control" pattern="^\d{10}$"
                        title="Enter a valid 10-digit phone number" required>

                </div>
            </div>

            <div class="row mb-3">

                <div class="col-md-6">
                    <label for="job_start_date" class="form-label">Job Start Date and Time</label>
                    <input type="datetime-local" name="job_start_date" id="job_start_date" class="form-control"
                        required>
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
    </div>

    <script>
        document.getElementById('contact_number').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, ''); 
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10); 
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>