<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

// DB connection
$conn = new mysqli("localhost", "root", "", "student_registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete handler
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $res = $conn->query("SELECT image FROM students WHERE id = $delete_id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $imageFile = 'uploads/' . $row['image'];
        if (file_exists($imageFile)) {
            unlink($imageFile);
        }
    }
    $conn->query("DELETE FROM students WHERE id = $delete_id");
    header("Location: register.php");
    exit;
}

// Form submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = $_POST['name'] ?? '';
    $email  = $_POST['email'] ?? '';
    $age    = $_POST['age'] ?? 0;
    $gender = $_POST['gender'] ?? '';
    $image  = $_FILES['image']['name'] ?? '';
    $temp   = $_FILES['image']['tmp_name'] ?? '';

    $folder = "uploads/";
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $targetPath = $folder . basename($image);

    if (move_uploaded_file($temp, $targetPath)) {
        $stmt = $conn->prepare("INSERT INTO students (name, email, age, gender, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $name, $email, $age, $gender, $image);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>🎉 Student registered successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Database error: " . $stmt->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ Failed to upload image.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Student Registration by:joneel </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            background-color: #fff;
            color: #333;
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }
        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 16px;
            transition: all 0.3s ease-in-out;
            border: 1px solid #ced4da;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 0.15rem rgba(106, 17, 203, 0.25);
            outline: none;
        }
        .btn-primary {
            border-radius: 12px;
            padding: 12px;
            font-weight: bold;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(to right, #5a0eb7, #1f63e0);
        }
        .table img {
            width: 50px;
            height: auto;
            border-radius: 5px;
        }
        .btn-delete {
            color: #fff;
            background-color: #dc3545;
            border: none;
            padding: 5px 12px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }
        .btn-delete:hover {
            background-color: #bb2d3b;
        }
    </style>
</head>
<body class="py-5">
<div class="text-end mb-3 container">
    <a href="admin_login.php?logout=1" class="btn btn-outline-light">🚪 Logout</a>
</div>

<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <div class="card p-4">
                <h2 class="text-center mb-4">📘 Student Registration Management</h2>
                <?= $message ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter full name..." required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email address..." required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Age</label>
                        <input type="number" name="age" min="1" max="150" class="form-control" placeholder="Enter age..." required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="" selected disabled>Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preview</label><br>
                        <img id="preview" src="#" alt="Image Preview" style="max-height: 100px; display: none;" class="img-thumbnail mt-2" />
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register Student</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card p-4">
                <h3 class="text-center mb-3">📋 Registered Students</h3>
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Image</th>
                            <th>Registered At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM students ORDER BY id DESC");
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= $row['age'] ?></td>
                                <td><?= $row['gender'] ?></td>
                                <td><img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="Student Image" /></td>
                                <td><?= $row['created_at'] ?></td>
                                <td>
                                    <a href="register.php?delete_id=<?= $row['id'] ?>"
                                       onclick="return confirm('Are you sure you want to delete this student?');"
                                       class="btn-delete" title="Delete Student">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No students registered yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Image Preview Script -->
<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function () {
        const preview = document.getElementById("preview");
        preview.src = reader.result;
        preview.style.display = "block";
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
</body>
</html>
