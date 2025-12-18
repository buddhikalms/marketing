<?php
session_start();
include 'config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = '$user_id'")->fetch_assoc();
$ref_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/register.php?ref=" . $user['referral_code'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
    .node-style {
        border: 2px solid #3498db;
        background: #fff;
        border-radius: 5px;
        padding: 5px;
        font-weight: bold;
    }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card p-3 mb-4 d-flex flex-row justify-content-between">
                    <h4>Welcome, <?php echo $_SESSION['username']; ?></h4>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>

                <div class="alert alert-info">
                    <strong>Your Referral Link:</strong> <?php echo $ref_link; ?>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">My Network Pyramid</div>
                    <div class="card-body">
                        <div id="chart_div"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    google.charts.load('current', {
        packages: ["orgchart"]
    });
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('string', 'Parent');
        data.addColumn('string', 'Tooltip');

        data.addRows([
            [{
                v: '<?php echo $user_id; ?>',
                f: '<div class="node-style">YOU (<?php echo $user['username']; ?>)</div>'
            }, '', 'Me'],
            <?php
                function getDownline($conn, $p_id)
                {
                    $p_id_safe = (int)$p_id;
                    $res = $conn->query("SELECT id, username, full_name, email, contact_number FROM users WHERE parent_id = $p_id_safe");
                    while ($row = $res->fetch_assoc()) {
                        $tooltip = 'Name: ' . $row['full_name'] . '\\n' .
                            'Email: ' . $row['email'] . '\\n' .
                            'Contact: ' . $row['contact_number'];

                        $node = "[{v:'" . $row['id'] . "', f:'<div class=\"node-style\">" . $row['username'] . "</div>'}, '" . $p_id . "', '" . $tooltip . "'],";
                        echo $node;

                        getDownline($conn, (int)$row['id']);
                    }
                }
                getDownline($conn, $user_id);
                ?>
        ]);

        var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
        chart.draw(data, {
            'allowHtml': true,
            'size': 'medium'
        });
    }
    </script>
</body>

</html>