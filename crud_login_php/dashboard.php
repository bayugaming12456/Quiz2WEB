<?php
session_start();
include 'koneksi/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Handle Create
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $profile_image = $_FILES['profile_image']['name'];
    $target_dir = "uploads/" . basename($profile_image);

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir)) {
        $query = "INSERT INTO users (username, password, profile_image) VALUES ('$username', '$password', '$profile_image')";
        mysqli_query($conn, $query);
    }
    header("Location: dashboard.php");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM users WHERE id=$id";
    mysqli_query($conn, $query);
    header("Location: dashboard.php");
}

// Handle Update
if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $_POST['existing_password'];
    $profile_image = $_FILES['profile_image']['name'] ? $_FILES['profile_image']['name'] : $_POST['existing_image'];
    $target_dir = "uploads/" . basename($profile_image);

    if ($_FILES['profile_image']['name']) {
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir);
    }

    $query = "UPDATE users SET username='$username', password='$password', profile_image='$profile_image' WHERE id=$id";
    mysqli_query($conn, $query);
    header("Location: dashboard.php");
}

// Fetch Users
$users = mysqli_query($conn, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">MyApp</a>
        <div class="d-flex">
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>
<div class="container mt-5">
    <h2>Manajemen Pengguna</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Tambah Pengguna</button>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Profile Image</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($users)) : ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= $user['username'] ?></td>
                <td>
                    <?php if ($user['profile_image']) : ?>
                        <img src="uploads/<?= $user['profile_image'] ?>" alt="Profile Image" class="rounded" width="50">
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $user['id'] ?>">Edit</button>
                    <a href="?delete=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</a>
                </td>
            </tr>

            <!-- Edit User Modal -->
            <div class="modal fade" id="editUserModal<?= $user['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Pengguna</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="existing_password" value="<?= $user['password'] ?>">
                                <input type="hidden" name="existing_image" value="<?= $user['profile_image'] ?>">
                                <div class="mb-3">
                                    <label>Username</label>
                                    <input type="text" name="username" value="<?= $user['username'] ?>" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label>Password (Kosongkan jika tidak diubah)</label>
                                    <input type="password" name="password" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label>Profile Image</label>
                                    <input type="file" name="profile_image" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update_user" class="btn btn-success">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Profile Image</label>
                        <input type="file" name="profile_image" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_user" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
