<?php
include 'db.php';
// Configure 10-hour session settings (36000 seconds)
ini_set('session.gc_maxlifetime', 36000);
ini_set('session.cookie_lifetime', 36000);
session_set_cookie_params([
    'lifetime' => 36000,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']), // Auto-detect HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

// Check for inactivity timeout (10 hours)
if (time() - $_SESSION['LAST_ACTIVITY'] > 36000) {
    session_unset();
    session_destroy();
    header('Location: login.php?reason=timeout');
    exit();
}

// Update last activity time stamp
$_SESSION['LAST_ACTIVITY'] = time();

// Check filter settings
$showHidden = isset($_GET['show_hidden']) && $_GET['show_hidden'] == 1;
$showFinished = isset($_GET['show_finished']) && $_GET['show_finished'] == 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All job events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <h1 class="mb-4">All job events</h1>

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
                <i class="fas fa-filter"></i> Apply Filters
            </button>
            <button class="btn btn-outline-secondary me-2" onclick="clearFilters()">
                <i class="fas fa-times-circle"></i> Clear Filters
            </button>
            <button id="toggleFinishedBtn"
                class="btn <?= $showFinished ? 'btn-success' : 'btn-outline-success' ?> me-2">
                <i class="fas <?= $showFinished ? 'fa-check-square' : 'fa-square' ?>"></i>
                <?= $showFinished ? 'Hide' : 'Show' ?> History
            </button>
            <button id="toggleHiddenBtn" class="btn <?= $showHidden ? 'btn-warning' : 'btn-outline-warning' ?>">
                <i class="fas <?= $showHidden ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                <?= $showHidden ? 'Hide' : 'Show' ?> Delete Jobs
            </button>
            <button id="toggleRestoredBtn" class="btn btn-outline-info">
                <i class="fas fa-undo"></i> Show Only Restored Jobs
            </button>
        </div>

        <table class="table table-bordered table-striped" id="jobTable">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Order Number</th>
                    <th>Company Name</th>
                    <th>Contact Number</th>
                    <th>Job Start Date</th>
                    <th>Deadline</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Completion Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM job_status WHERE 1=1";

                if (!$showHidden) {
                    $sql .= " AND hidden = 0";
                }

                if ($showFinished) {
                    $sql .= " AND status = 'Finished'";
                }

                $sql .= " ORDER BY completion_date DESC";

                $result = $conn->query($sql);
                $count = 1; // Initialize counter
                while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?= $row['hidden'] ? 'hidden-row' : '' ?>">
                        <td class="text-center"><?= $count++ ?></td> <!-- Display and increment counter -->
                        <td><?= htmlspecialchars($row['order_number']) ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td><?= htmlspecialchars($row['contact_number']) ?></td>
                        <td><?= $row['job_start_date'] ?></td>
                        <td><?= $row['deadline'] ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= isset($row['completion_date']) ? $row['completion_date'] : 'N/A' ?></td>
                        <td class="status-cell text-white text-center">
                            <select class="form-select status-dropdown" data-id="<?= $row['id'] ?>">
                                <option value="Call" <?= ($row['status'] == 'Call') ? 'selected' : '' ?>>Call</option>
                                <option value="Chat" <?= ($row['status'] == 'Chat') ? 'selected' : '' ?>>Chat</option>
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
                        <td class="action-cell">
                            <?php if ($row['hidden']): ?>
                                <button class="btn btn-sm btn-success" onclick="restoreJob(<?= $row['id'] ?>)">
                                    <i class="fas fa-undo"></i> Restore
                                </button>
                            <?php endif; ?>
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

        .hidden-row {
            background-color: #f8f9fa !important;
            color: #6c757d !important;
        }

        .hidden-row td {
            opacity: 0.7;
        }

        .action-cell {
            text-align: center;
        }

        .status-dropdown option[value="Call"] {
            background-color: #a6d7ff;
            color: black;
        }

        .status-dropdown option[value="Chat"] {
            background-color: #ffb6c1;
            color: black;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        function restoreJob(id) {
            if (confirm('Restore this job to active jobs?')) {
                fetch('restore_job.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Job restored successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchBar').value.toLowerCase();
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const rows = document.querySelectorAll('#jobTable tbody tr');

            rows.forEach(row => {
                const orderNumber = row.cells[1].textContent.toLowerCase();
                const companyName = row.cells[2].textContent.toLowerCase();
                const matchesSearch = !searchTerm ||
                    orderNumber.includes(searchTerm) ||
                    companyName.includes(searchTerm);

                const completionDateText = row.cells[8].textContent.trim();
                let matchesDate = true;

                if (dateFrom || dateTo) {
                    if (completionDateText === 'N/A') {
                        matchesDate = false;
                    } else {
                        try {
                            const rowDate = new Date(completionDateText);

                            // Normalize time components for proper date comparison
                            rowDate.setHours(0, 0, 0, 0);

                            if (dateFrom) {
                                const fromDate = new Date(dateFrom);
                                fromDate.setHours(0, 0, 0, 0);
                                if (rowDate < fromDate) {
                                    matchesDate = false;
                                }
                            }

                            if (dateTo && matchesDate) { // Only check if still matching
                                const toDate = new Date(dateTo);
                                toDate.setHours(23, 59, 59, 999); // End of day
                                if (rowDate > toDate) {
                                    matchesDate = false;
                                }
                            }
                        } catch (e) {
                            console.error("Date parsing error:", e);
                            matchesDate = false;
                        }
                    }
                }

                row.style.display = (matchesSearch && matchesDate) ? '' : 'none';
            });
        }

        // Update the clearFilters function to reset properly
        function clearFilters() {
            document.getElementById('searchBar').value = '';
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            const rows = document.querySelectorAll('#jobTable tbody tr');
            rows.forEach(row => {
                row.style.display = '';
            });
        }
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById('searchBar').addEventListener('keyup', applyFilters);
            document.getElementById('dateFrom').addEventListener('change', applyFilters);
            document.getElementById('dateTo').addEventListener('change', applyFilters);

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
                                location.reload();
                            } else {
                                alert("Error updating status.");
                            }
                        });
                });
            });

            // Toggle hidden jobs
            document.getElementById('toggleHiddenBtn').addEventListener('click', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('show_hidden', <?= $showHidden ? '0' : '1' ?>);
                window.location.href = url.toString();
            });

            // Toggle finished jobs
            document.getElementById('toggleFinishedBtn').addEventListener('click', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('show_finished', <?= $showFinished ? '0' : '1' ?>);
                window.location.href = url.toString();
            });

            document.getElementById('toggleRestoredBtn').addEventListener('click', function () {
                const rows = document.querySelectorAll('#jobTable tbody tr');
                rows.forEach(row => {
                    const isRestored = row.querySelector('.btn-success') !== null; // Restore button ඇතිද බලන්න
                    row.style.display = isRestored ? '' : 'none';
                });
            });

            applyFilters();
        });

        function setDropdownColor(dropdown) {
            let statusColor = {
                "Call": "#a6d7ff",
                "Chat": "#ffb6c1",
                "NotPaid": "#d889f7",
                "Design": "#f0ad4e",
                "Confirmation": "#5bc0de",
                "Print": "#09e9cb",
                "Delivery": "#5cb85c",
                "Finished": "#d9534f"
            };
            dropdown.style.backgroundColor = statusColor[dropdown.value] || "white";
            dropdown.style.color = "white";
        }
    </script>
</body>

</html>