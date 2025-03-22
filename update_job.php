<?php
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['jobId'];
    $order_number = $_POST['editOrderNumber'];
    $company_name = $_POST['editCompanyName'];
    $contactNumber = $_POST['editContactNumber'];
    $category = $_POST['editCategory'];  // Get the category
    $quantity = $_POST['editQuantity'];  // Get the quantity
    $job_start_date = $_POST['editJobStartDate'];
    $deadline = $_POST['editDeadline'];

    // Convert datetime-local format to database format
    $job_start_date = str_replace("T", " ", $job_start_date);
    $deadline = str_replace("T", " ", $deadline);

    // Exclude 'status' from the update query
    $sql = "UPDATE job_status SET order_number = ?, company_name = ?, contact_number = ?, category = ?, quantity = ?, job_start_date = ?, deadline = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssi', $order_number, $company_name, $contactNumber, $category, $quantity, $job_start_date, $deadline, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Job updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating job: ' . $stmt->error]);
    }

    $stmt->close();
}
$conn->close();
?>
