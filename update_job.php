<?php
include 'db.php'; 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['jobId'];
    $order_number = $_POST['editOrderNumber'];
    $company_name = $_POST['editCompanyName'];
    $contactNumber = $_POST['editContactNumber'];
    $job_start_date = $_POST['editJobStartDate'];
    $deadline = $_POST['editDeadline'];
    $status = $_POST['editStatus'];
    $job_start_date = str_replace("T", " ", $job_start_date);
    $deadline = str_replace("T", " ", $deadline);

    $sql = "UPDATE JobStatus SET order_number = ?, company_name = ?, contact_number = ?, job_start_date = ?, deadline = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param('ssssssi', $order_number, $company_name, $contactNumber, $job_start_date, $deadline, $status, $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Job updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating job: ' . $stmt->error]);
    }
    $stmt->close();
}
$conn->close(); 
?>