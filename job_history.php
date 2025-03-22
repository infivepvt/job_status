<?php
include 'db.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500&display=swap" rel="stylesheet">

</head>

<body>
    <?php include('header.php'); ?>

    <div class="container mt-5">
        <h1 class="mb-4">Job Status History</h1>
        <div class="mb-4">
            <input type="text" id="searchBar" class="form-control" placeholder="Search by Order Number or Company Name"
                onkeyup="searchJobs()">
        </div>

        <table class="table table-bordered table-striped" id="jobTable">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Company Name</th>
                    <th>Contact Number</th>
                    <th>Job Start Date</th>
                    <th>Deadline</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Date</th>
                    <th>Status</th>

                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM job_status 
                ORDER BY FIELD(status, 'date') DESC, date DESC";

                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['order_number'] ?></td>
                        <td><?= $row['company_name'] ?></td>
                        <td><?= $row['contact_number'] ?></td>
                        <td><?= $row['job_start_date'] ?></td>
                        <td><?= $row['deadline'] ?></td>
                        <td><?= $row['category'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= $row['date'] ?></td>
                        <td class="status-cell text-white text-center">
                            <select class="form-select status-dropdown" data-id="<?= $row['id'] ?>">
                                <option value="NotYet" <?= ($row['status'] == 'NotYet') ? 'selected' : '' ?>>Not Yet</option>
                                <option value="Design" <?= ($row['status'] == 'Design') ? 'selected' : '' ?>>Design</option>
                                <option value="Confirmation" <?= ($row['status'] == 'Confirmation') ? 'selected' : '' ?>>
                                    Confirmation</option>
                                <option value="Print" <?= ($row['status'] == 'Print') ? 'selected' : '' ?>>Print</option>
                                <option value="Delivery" <?= ($row['status'] == 'Delivery') ? 'selected' : '' ?>>Delivery
                                </option>
                                <option value="Finished" <?= ($row['status'] == 'Finished') ? 'selected' : '' ?>>Finished
                                </option>
                            </select>
                        </td>



                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <style>
        .status-dropdown {
            border: 1px solid #ccc;
            padding: 5px;
        }

        .status-dropdown option[value="NotYet"] {
            background-color: #d889f7;
            color: black;
        }

        .status-dropdown option[value="Design"] {
            background-color: #f0ad4e;
            color: black;
        }

        .status-dropdown option[value="Confirmation"] {
            background-color: #5bc0de;
            color: black;
        }

        .status-dropdown option[value="Print"] {
            background-color: #09e9cb;
            color: white;
        }

        .status-dropdown option[value="Delivery"] {
            background-color: #5cb85c;
            color: white;
        }

        .status-dropdown option[value="Finished"] {
            background-color: #d9534f;
            color: white;
        }

        #jobTable tbody td {
            font-weight: 100;
            font-family: 'Rubik', sans-serif;
        }


        #jobTable tbody tr:hover {
            background-color: rgb(103, 166, 233);
            cursor: pointer;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        function searchJobs() {
            const searchTerm = document.getElementById('searchBar').value.toLowerCase();
            const rows = document.querySelectorAll('#jobTable tbody tr');
            rows.forEach(row => {
                const orderNumber = row.cells[0].textContent.toLowerCase();
                const companyName = row.cells[1].textContent.toLowerCase();
                if (orderNumber.includes(searchTerm) || companyName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".status-dropdown").forEach(dropdown => {
                setDropdownColor(dropdown);

                dropdown.addEventListener("change", function () {
                    const jobId = this.getAttribute("data-id");
                    const newStatus = this.value;

                    fetch("update_status.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `id=${jobId}&status=${newStatus}`
                    })
                        .then(response => response.text())
                        .then(data => {
                            if (data === "success") {
                                alert("Status updated successfully!");
                                setDropdownColor(this);
                            } else {
                                alert("Error updating status.");
                            }
                        });
                });
            });
        });

        function setDropdownColor(dropdown) {
            let statusColor = {
                "Design": "#f0ad4e",
                "Confirmation": "#5bc0de",
                "Print": "#09e9cb",
                "Delivery": "#5cb85c",
                "Finished": "#d9534f",
                "NotYet": "#d889f7"
            };

            dropdown.style.backgroundColor = statusColor[dropdown.value] || "white";
            dropdown.style.color = "white";
        }

    </script>
</body>

</html>