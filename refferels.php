<?php
session_start();
$page_title = "My Network";
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT username, referral_code FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

$ref_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/register.php?ref=" . $user['referral_code'];

include 'templates/header.php';
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<style>
    .node-style {
        border: 2px solid #3498db;
        background: #fff;
        border-radius: 5px;
        padding: 5px;
        font-weight: bold;
    }

    #chart_div .google-visualization-orgchart-node {
        cursor: pointer;
    }

    .modal-body p {
        margin-bottom: 0.5rem;
    }

    .modal-body p strong {
        display: inline-block;
        width: 120px;
    }
</style>

<div class="card shadow">
    <div class="card-header">My Network Pyramid</div>
    <div class="card-body">
        <div id="chart_div" style="width: 100%; min-height: 500px;"></div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBodyContent">
                <!-- User details will be loaded here -->
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
            }, '', 'Your Account'],
            <?php
            function getDownline($conn, $p_id)
            {
                $stmt = $conn->prepare("SELECT id, username, full_name FROM users WHERE parent_id = ?");
                $stmt->bind_param("i", $p_id);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $tooltip = 'Click to see details for ' . $row['full_name'];
                    $node = "[{v:'" . $row['id'] . "', f:'<div class=\"node-style\">" . $row['username'] . "</div>'}, '" . $p_id . "', '" . $tooltip . "'],";
                    echo $node;
                    getDownline($conn, (int)$row['id']);
                }
                $stmt->close();
            }
            getDownline($conn, $user_id);
            ?>
        ]);

        var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));

        // Event listener for when a node is clicked
        google.visualization.events.addListener(chart, 'select', function() {
            var selection = chart.getSelection();
            if (selection.length > 0) {
                var selectedRow = selection[0].row;
                var userId = data.getValue(selectedRow, 0);
                showUserDetails(userId);
            }
        });

        chart.draw(data, {
            'allowHtml': true,
            'size': 'medium'
        });
    }

    function showUserDetails(userId) {
        var modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
        var modalBody = document.getElementById('modalBodyContent');
        modalBody.innerHTML = '<p class="text-center">Loading...</p>';
        modal.show();

        fetch('get_user_details.php?id=' + userId)
            .then(response => response.json())
            .then(data => {
                // Handle permission errors or other issues from the server
                if (data.error) {
                    modalBody.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                } else {
                    // Format numbers to currency for display
                    const totalSales = parseFloat(data.total_sales).toFixed(2);
                    const totalCommissions = parseFloat(data.total_commissions).toFixed(2);

                    modalBody.innerHTML = `
                            <div class="alert alert-success">
                                <strong>Sales:</strong> ${data.sales_count} units / $${totalSales}<br>
                                <strong>Commissions:</strong> $${totalCommissions}
                            </div>
                            <p><strong>Username:</strong> ${data.username || 'N/A'}</p>
                            <p><strong>Full Name:</strong> ${data.full_name || 'N/A'}</p>
                            <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                            <p><strong>Contact:</strong> ${data.contact_number || 'N/A'}</p>
                            <p><strong>NIC:</strong> ${data.nic_number || 'N/A'}</p>
                            <p><strong>Gender:</strong> ${data.gender || 'N/A'}</p>
                            <p><strong>District:</strong> ${data.district || 'N/A'}</p>
                            <p><strong>City:</strong> ${data.city || 'N/A'}</p>
                            <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                            <p><strong>Joined On:</strong> ${new Date(data.created_at).toLocaleDateString()}</p>
                        `;
                }
            })
            .catch(error => {
                modalBody.innerHTML =
                    '<div class="alert alert-danger">An error occurred while fetching user details.</div>';
                console.error('Error:', error);
            });
    }
</script>

<?php
include 'templates/footer.php';
?>