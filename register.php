<?php
session_start();
include 'config/db.php';

$ref_from_url = isset($_GET['ref']) ? $_GET['ref'] : '';
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $nic_number = $_POST['nic_number'];
    $gender = $_POST['gender'];
    $district = $_POST['district'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];
    $parent_ref_code = $_POST['referral_code'];

    // Aluth userta unique code ekak hadima
    $my_referral_code = "REF" . strtoupper(substr(md5(uniqid()), 0, 6));

    // Referral code eka check kirima
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
    $stmt_check->bind_param("s", $parent_ref_code);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $parent_id = $result->fetch_assoc()['id'];

        $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password, full_name, nic_number, gender, district, city, address, contact_number, referral_code, parent_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssssssssssi", $username, $email, $password, $full_name, $nic_number, $gender, $district, $city, $address, $contact_number, $my_referral_code, $parent_id);

        if ($stmt_insert->execute()) {
            $success = "Registration Successful! Log in to continue.";
        } else {
            $error = "Registration failed. Email or Username might already exist.";
        }
        $stmt_insert->close();
    } else {
        $error = "Invalid Referral Code! A valid referral is required.";
    }
    $stmt_check->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register - Marketing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5 card p-4 shadow">
                <h3 class="text-center">Join Our Network</h3>
                <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
                <form method="POST">
                    <div class="mb-3"><input type="text" name="username" class="form-control" placeholder="Username"
                            required></div>
                    <div class="mb-3"><input type="text" name="full_name" class="form-control" placeholder="Full Name"
                            required></div>
                    <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email Address"
                            required></div>
                    <div class="mb-3"><input type="text" name="contact_number" class="form-control" placeholder="Contact Number">
                    </div>
                    <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password"
                            required></div>
                    <div class="mb-3"><input type="text" name="nic_number" class="form-control" placeholder="NIC Number">
                    </div>
                    <div class="mb-3">
                        <select name="gender" class="form-select">
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3"><input type="text" name="district" class="form-control" placeholder="District">
                    </div>
                    <div class="mb-3"><input type="text" name="city" class="form-control" placeholder="City">
                    </div>
                    <div class="mb-3">
                        <textarea name="address" class="form-control" placeholder="Address"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Referral Code (Required)</label>
                        <input type="text" name="referral_code" class="form-control"
                            value="<?php echo $ref_from_url; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register Now</button>
                </form>
                <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</body>

</html>