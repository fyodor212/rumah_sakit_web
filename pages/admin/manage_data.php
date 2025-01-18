<?php
require 'config/database.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: index.php?page=auth/login');
    exit;
}

$type = $_GET['type'] ?? 'dokter'; // dokter/pasien/layanan/users

// Handle POST requests untuk simpan data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tipe'])) {
        $tipe = $_POST['tipe'];
        
        switch($tipe) {
            case 'pasien':
                $nama = $_POST['nama'];
                $alamat = $_POST['alamat'] ?? '';
                $no_hp = $_POST['no_hp'] ?? '';
                try {
                    $no_rm = generatePatientRM($db);
                    $query = "INSERT INTO pasien (no_rm, nama, alamat, no_hp) VALUES (?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    
                    if ($stmt->execute([$no_rm, $nama, $alamat, $no_hp])) {
                        $_SESSION['success'] = "Pasien berhasil ditambahkan!";
                    } else {
                        $_SESSION['error'] = "Gagal menambahkan pasien.";
                    }
                } catch(PDOException $e) {
                    $_SESSION['error'] = "Error: " . $e->getMessage();
                }
                break;

            case 'users':
                $username = $_POST['username'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role = $_POST['role'];
                try {
                    $query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
                    $stmt = $db->prepare($query);
                    
                    if ($stmt->execute([$username, $password, $role])) {
                        $_SESSION['success'] = "Pengguna berhasil ditambahkan!";
                    } else {
                        $_SESSION['error'] = "Gagal menambahkan pengguna.";
                    }
                } catch(PDOException $e) {
                    $_SESSION['error'] = "Error: " . $e->getMessage();
                }
                break;

            case 'layanan':
                $nama = $_POST['nama'];
                $deskripsi = $_POST['deskripsi'];
                $harga = $_POST['harga'];
                try {
                    $query = "INSERT INTO layanan (nama, deskripsi, harga) VALUES (?, ?, ?)";
                    $stmt = $db->prepare($query);
                    
                    if ($stmt->execute([$nama, $deskripsi, $harga])) {
                        $_SESSION['success'] = "Layanan berhasil ditambahkan!";
                    } else {
                        $_SESSION['error'] = "Gagal menambahkan layanan.";
                    }
                } catch(PDOException $e) {
                    $_SESSION['error'] = "Error: " . $e->getMessage();
                }
                break;
        }
    }
    
    // Handle Update
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        switch($type) {
            case 'pasien':
                $nama = $_POST['nama'];
                $alamat = $_POST['alamat'];
                $no_hp = $_POST['no_hp'];
                try {
                    $query = "UPDATE pasien SET nama = ?, alamat = ?, no_hp = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$nama, $alamat, $no_hp, $id]);
                    $_SESSION['success'] = "Data pasien berhasil diupdate!";
                } catch(PDOException $e) {
                    $_SESSION['error'] = "Error: " . $e->getMessage();
                }
                break;
                
            case 'users':
                $username = $_POST['username'];
                $role = $_POST['role'];
                try {
                    $query = "UPDATE users SET username = ?, role = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$username, $role, $id]);
                    $_SESSION['success'] = "Data pengguna berhasil diupdate!";
                } catch(PDOException $e) {
                    $_SESSION['error'] = "Error: " . $e->getMessage();
                }
                break;
        }
    }
    
    header("Location: ?page=manage_data&type=$type");
    exit;
}

// Handle delete action
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $db->beginTransaction();
        
        switch($type) {
            case 'pasien':
                deletePatient($db, $id);
                break;
                
            case 'users':
                $query = "DELETE FROM users WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
                break;
                
            case 'layanan':
                $query = "DELETE FROM layanan WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
                break;
        }
        
        $db->commit();
        $_SESSION['success'] = "Data berhasil dihapus";
    } catch(Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "Gagal menghapus data: " . $e->getMessage();
    }
    header("Location: ?page=manage_data&type=$type");
    exit;
}

// Get data based on type
function getData($type) {
    global $db;
    switch($type) {
        case 'pasien':
            $query = "SELECT p.*, u.username, u.email FROM pasien p 
                     LEFT JOIN users u ON p.user_id = u.id ORDER BY p.no_rm DESC";
            break;
        case 'users':
            $query = "SELECT * FROM users ORDER BY username ASC";
            break;
        case 'layanan':
            $query = "SELECT * FROM layanan ORDER BY nama ASC";
            break;
    }
    return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

$data = getData($type);
?>

<main>
    <div class="content">
        <div class="page-header">
            <h2>Kelola <?= ucfirst($type) ?></h2>
            <?php if($type == 'dokter'): ?>
                <a href="?page=admin/manage_doctor" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Kelola Dokter
                </a>
            <?php endif; ?>
        </div>

        <!-- Form Tambah Data -->
        <?php if(in_array($type, ['pasien', 'users', 'layanan'])): ?>
            <form action="" method="POST" class="mb-4">
                <input type="hidden" name="tipe" value="<?= $type ?>">
                
                <?php if($type == 'pasien'): ?>
                    <div class="form-group">
                        <label>Nama Pasien</label>
                        <input type="text" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" name="alamat">
                    </div>
                    <div class="form-group">
                        <label>No HP</label>
                        <input type="text" name="no_hp">
                    </div>
                
                <?php elseif($type == 'users'): ?>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                
                <?php elseif($type == 'layanan'): ?>
                    <div class="form-group">
                        <label>Nama Layanan</label>
                        <input type="text" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" name="harga" required>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </form>
        <?php endif; ?>

        <!-- Alert Messages -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <?php if($type == 'pasien'): ?>
                            <th>No. RM</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        <?php elseif($type == 'users'): ?>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        <?php elseif($type == 'layanan'): ?>
                            <th>Nama Layanan</th>
                            <th>Deskripsi</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data as $item): ?>
                        <tr>
                            <?php if($type == 'pasien'): ?>
                                <td><?= htmlspecialchars($item['no_rm']) ?></td>
                                <td><?= htmlspecialchars($item['nama']) ?></td>
                                <td><?= htmlspecialchars($item['email'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($item['no_hp']) ?></td>
                                <td><?= htmlspecialchars($item['alamat']) ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" 
                                            onclick="editData('pasien', <?= $item['id'] ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="?page=manage_data&type=pasien&action=delete&id=<?= $item['id'] ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            <?php elseif($type == 'users'): ?>
                                <td><?= htmlspecialchars($item['username']) ?></td>
                                <td><?= htmlspecialchars($item['role']) ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" 
                                            onclick="editData('users', <?= $item['id'] ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="?page=manage_data&type=users&action=delete&id=<?= $item['id'] ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            <?php elseif($type == 'layanan'): ?>
                                <td><?= htmlspecialchars($item['nama']) ?></td>
                                <td><?= htmlspecialchars($item['deskripsi']) ?></td>
                                <td><?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <a href="?page=edit_layanan&id=<?= $item['id'] ?>" 
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?page=manage_data&type=layanan&action=delete&id=<?= $item['id'] ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Yakin ingin menghapus layanan ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main> 

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="update" value="1">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body" id="editFormContent">
                    <!-- Form fields will be inserted here dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editData(type, id) {
    // Fetch data and populate modal
    fetch(`?page=manage_data&type=${type}&action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editId').value = data.id;
            let content = '';
            
            if(type === 'pasien') {
                content = `
                    <div class="form-group">
                        <label>Nama Pasien</label>
                        <input type="text" name="nama" value="${data.nama}" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" name="alamat" value="${data.alamat}">
                    </div>
                    <div class="form-group">
                        <label>No HP</label>
                        <input type="text" name="no_hp" value="${data.no_hp}">
                    </div>
                `;
            } else if(type === 'users') {
                content = `
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="${data.username}" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="admin" ${data.role === 'admin' ? 'selected' : ''}>Admin</option>
                            <option value="staff" ${data.role === 'staff' ? 'selected' : ''}>Staff</option>
                            <option value="user" ${data.role === 'user' ? 'selected' : ''}>User</option>
                        </select>
                    </div>
                `;
            }
            
            document.getElementById('editFormContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
}
</script>