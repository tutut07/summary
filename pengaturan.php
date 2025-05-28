<?php
$pageTitle = "Pengaturan Profil";
include 'header.php';  // Panggil header

require 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error . "<br>Query: " . $sql);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User tidak ditemukan");
}

$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newEmail = $_POST['email'];
    $newPassword = $_POST['password'];

    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateSql = "UPDATE users SET email = ?, password = ? WHERE username = ?";
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt === false) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $updateStmt->bind_param("sss", $newEmail, $hashedPassword, $username);
    } else {
        $updateSql = "UPDATE users SET email = ? WHERE username = ?";
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt === false) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $updateStmt->bind_param("ss", $newEmail, $username);
    }

    if ($updateStmt->execute()) {
        echo "<div class='alert alert-success'>Profil berhasil diperbarui.</div>";
        $user['email'] = $newEmail;
    } else {
        echo "<div class='alert alert-danger'>Gagal memperbarui profil.</div>";
    }
}

?>

<h2>Pengaturan Profil</h2>

<form method="post" action="">
  <div class="mb-3">
    <label for="username" class="form-label">Username</label>
    <input type="text" id="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
  </div>

  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password baru">
  </div>

  <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
</form>

<?php include 'footer.php';  // Panggil footer ?>
