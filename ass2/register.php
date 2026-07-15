<?php
require_once 'dbconfig.php.inc';

$errors = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $dob = trim($_POST['date_of_birth'] ?? '');
    $flat = trim($_POST['flat_unit'] ?? '');
    $street = trim($_POST['street_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $postal = trim($_POST['postal_code'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'Customer';

    // Store form data for redisplay
    $form_data = compact('first_name', 'last_name', 'email', 'mobile', 'dob', 'flat', 'street', 'city', 'country', 'postal', 'role');

    // Validation
    if (!$first_name) $errors[] = 'First Name is required.';
    if (!$last_name) $errors[] = 'Last Name is required.';
    if (!$email) $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email format is invalid.';
    if (!$mobile) $errors[] = 'Mobile Number is required.';
    if (!$dob) $errors[] = 'Date of Birth is required.';
    elseif (!$errors) {
        $dob_obj = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dob_obj || $dob_obj >= new DateTime()) {
            $errors[] = 'Date of Birth must be a valid date in the past.';
        } else {
            $today = new DateTime();
            $age = $today->diff($dob_obj)->y;
            if ($age < 18) $errors[] = 'You must be at least 18 years old.';
        }
    }
    if (!$street) $errors[] = 'Street Address is required.';
    if (!$city) $errors[] = 'City is required.';
    if (!$country) $errors[] = 'Country is required.';
    if (!$postal) $errors[] = 'Postal Code is required.';
    elseif (!ctype_digit($postal) || strlen($postal) !== 6) {
        $errors[] = 'Postal Code must be exactly 6 numeric digits.';
    }
    if (!$password) $errors[] = 'Password is required.';
    if ($password !== $confirm_pass) $errors[] = 'Password and Confirm Password must match.';

    // Check email uniqueness
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) $errors[] = 'Email already exists in the system.';
    }

    // Insert if no errors
    if (!$errors) {
        $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('
            INSERT INTO users (first_name, last_name, email, mobile, date_of_birth, flat_unit, street_address, city, country, postal_code, password, role)
            VALUES (:first, :last, :email, :mobile, :dob, :flat, :street, :city, :country, :postal, :pass, :role)
        ');
        $stmt->execute([
            ':first' => $first_name,
            ':last' => $last_name,
            ':email' => $email,
            ':mobile' => $mobile,
            ':dob' => $dob,
            ':flat' => $flat,
            ':street' => $street,
            ':city' => $city,
            ':country' => $country,
            ':postal' => $postal,
            ':pass' => $hashed_pass,
            ':role' => $role
        ]);
        $user_id = $pdo->lastInsertId();
        
        // Generate 10-digit ID (formatted)
        $display_id = str_pad($user_id, 10, '0', STR_PAD_LEFT);
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <main>
        <article>
            <h2>Registration Successful</h2>
            <p>Welcome to the Palestinian Souvenir Store!</p>
            <p>Your User ID is: <strong><?= htmlspecialchars($display_id) ?></strong></p>
            <p><a href="login.php">Proceed to Login</a></p>
        </article>
    </main>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
        <?php
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <main>
        <article>
            <h2>Create Your Account</h2>
            <?php if ($errors): ?>
                <section>
                    <h3>Errors:</h3>
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
            <form method="post" action="register.php">
                <fieldset>
                    <legend>Personal Information</legend>
                    <p>
                        <label>First Name (required):</label><br>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($form_data['first_name'] ?? '') ?>" required>
                    </p>
                    <p>
                        <label>Last Name (required):</label><br>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($form_data['last_name'] ?? '') ?>" required>
                    </p>
                    <p>
                        <label>Email Address (required):</label><br>
                        <input type="email" name="email" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
                    </p>
                    <p>
                        <label>Mobile Number (required):</label><br>
                        <input type="text" name="mobile" value="<?= htmlspecialchars($form_data['mobile'] ?? '') ?>" required>
                    </p>
                    <p>
                        <label>Date of Birth (required):</label><br>
                        <input type="date" name="date_of_birth" value="<?= htmlspecialchars($form_data['dob'] ?? '') ?>" required>
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Address Information</legend>
                    <p>
                        <label>Flat / Unit No (optional):</label><br>
                        <input type="text" name="flat_unit" value="<?= htmlspecialchars($form_data['flat'] ?? '') ?>">
                    </p>
                    <p>
                        <label>Street Name & No (required):</label><br>
                        <input type="text" name="street_address" value="<?= htmlspecialchars($form_data['street'] ?? '') ?>" required>
                    </p>
                    <p>
                        <label>City (required):</label><br>
                        <input type="text" name="city" value="<?= htmlspecialchars($form_data['city'] ?? '') ?>" required>
                    </p>
                    <p>
                        <label>Country (required):</label><br>
                        <input type="text" name="country" value="<?= htmlspecialchars($form_data['country'] ?? '') ?>" required>
                    </p>
                    <p>
                        <label>Postal Code - 6 digits (required):</label><br>
                        <input type="text" name="postal_code" value="<?= htmlspecialchars($form_data['postal'] ?? '') ?>" maxlength="6" required>
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Account Setup</legend>
                    <p>
                        <label>Password (required):</label><br>
                        <input type="password" name="password" required>
                    </p>
                    <p>
                        <label>Confirm Password (required):</label><br>
                        <input type="password" name="confirm_password" required>
                    </p>
                    <p>
                        <label>Role:</label><br>
                        <input type="radio" name="role" value="Customer" <?= ($form_data['role'] ?? 'Customer') === 'Customer' ? 'checked' : '' ?>> Customer
                        <input type="radio" name="role" value="Employee" <?= ($form_data['role'] ?? 'Customer') === 'Employee' ? 'checked' : '' ?>> Employee
                    </p>
                </fieldset>
                <p>
                    <button type="submit">Register</button>
                </p>
            </form>
            <p><a href="login.php">Already have an account? Login here.</a></p>
        </article>
    </main>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
