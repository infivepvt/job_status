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

        <!-- Filter Controls -->
        <div class="mb-4 row g-3">
            <div class="col-md-6">
                <input type="text" id="searchBar" class="form-control"
                    placeholder="Search by Order Number or Company Name">
            </div>
            <div class="col-md-3">
                <input type="date" id="dateFrom" class="form-control" placeholder="From Date">
            </div>
            <div class="col-md-3">
                <input type="date" id="dateTo" class="form-control" placeholder="To Date">
            </div>
        </div>

        <div class="mb-4">
            <button class="btn btn-primary me-2" onclick="applyFilters()">
                <i class="bi bi-funnel"></i> Apply Filters
            </button>
            <button class="btn btn-outline-secondary" onclick="clearFilters()">
                <i class="bi bi-x-circle"></i> Clear Filters
            </button>
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
                    <th>Completion Date</th>
                    <th>Status</th>

                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM job_status WHERE status = 'Finished' ORDER BY date DESC";


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
                        <td><?= isset($row['completion_date']) ? $row['completion_date'] : 'N/A' ?></td>
                        <td class="status-cell text-white text-center">
                            <select class="form-select status-dropdown" data-id="<?= $row['id'] ?>">
                                <option value="NotPaid" <?= ($row['status'] == 'NotPaid') ? 'selected' : '' ?>>Not Paid
                                </option>
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

        .status-dropdown option[value="NotPaid"] {
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

        /* Add this to your existing style section */
        #dateFrom,
        #dateTo {
            margin-left: 5px;
            margin-right: 5px;
        }

        .btn-primary {
            margin-right: 10px;
        }

        #searchBar,
        #dateFrom,
        #dateTo {
            transition: all 0.3s ease;
        }

        #searchBar:focus,
        #dateFrom:focus,
        #dateTo:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-outline-secondary {
            border-color: #6c757d;
            color: #6c757d;
        }

        @media (max-width: 768px) {

            .col-md-6,
            .col-md-3 {
                margin-bottom: 10px;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
          function applyFilters() {
            const searchTerm = document.getElementById('searchBar').value.toLowerCase();
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const rows = document.querySelectorAll('#jobTable tbody tr');
            
            rows.forEach(row => {
                // Text search check
                const orderNumber = row.cells[0].textContent.toLowerCase();
                const companyName = row.cells[1].textContent.toLowerCase();
                const matchesSearch = !searchTerm || 
                                     orderNumber.includes(searchTerm) || 
                                     companyName.includes(searchTerm);
                
                // Date range check
                const completionDateText = row.cells[7].textContent.trim();
                let matchesDate = true;
                
                if (dateFrom || dateTo) {
                    if (completionDateText === 'N/A') {
                        matchesDate = false;
                    } else {
                        try {
                            const rowDate = new Date(completionDateText);
                            const fromDate = dateFrom ? new Date(dateFrom) : null;
                            const toDate = dateTo ? new Date(dateTo) : null;
                            
                            if (fromDate) {
                                rowDate.setHours(0, 0, 0, 0);
                                fromDate.setHours(0, 0, 0, 0);
                                matchesDate = matchesDate && (rowDate >= fromDate);
                            }
                            if (toDate) {
                                const endOfDay = new Date(toDate);
                                endOfDay.setHours(23, 59, 59, 999);
                                matchesDate = matchesDate && (rowDate <= endOfDay);
                            }
                        } catch (e) {
                            matchesDate = false;
                        }
                    }
                }
                
                // Show/hide based on both conditions
                row.style.display = (matchesSearch && matchesDate) ? '' : 'none';
            });
        }

        // Clear all filters
        function clearFilters() {
            document.getElementById('searchBar').value = '';
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            applyFilters(); // This will show all rows now
        }

        // Initialize with event listeners
        document.addEventListener("DOMContentLoaded", function() {
            // Apply filters when search or date inputs change
            document.getElementById('searchBar').addEventListener('keyup', applyFilters);
            document.getElementById('dateFrom').addEventListener('change', applyFilters);
            document.getElementById('dateTo').addEventListener('change', applyFilters);
            
            // Your existing status dropdown initialization
            document.querySelectorAll(".status-dropdown").forEach(dropdown => {
                setDropdownColor(dropdown);
                
                dropdown.addEventListener("change", function() {
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
                            location.reload();
                        } else {
                            alert("Error updating status.");
                        }
                    });
                });
            });
            
            // Apply filters on initial page load
            applyFilters();
        });

        function setDropdownColor(dropdown) {
            let statusColor = {
                "Design": "#f0ad4e",
                "Confirmation": "#5bc0de",
                "Print": "#09e9cb",
                "Delivery": "#5cb85c",
                "Finished": "#d9534f",
                "NotPaid": "#d889f7"
            };
            dropdown.style.backgroundColor = statusColor[dropdown.value] || "white";
            dropdown.style.color = "white";
        }
    </script>

    </script>
</body>

</html>