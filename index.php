<?php
/**
 * Chuyên đề ma trận - PHP thuần
 * by Kha Minh Đăng
 * 
 * Hướng dẫn chạy:
 * 1. Copy thư mục matrix-app vào htdocs (XAMPP) hoặc www (Laragon)
 * 2. Truy cập http://localhost/matrix-app/
 * 3. Nhập dữ liệu theo placeholder mẫu để kiểm tra
 * 
 * Mẫu test:
 * - Ma trận 3x3 (det ≠ 0): 2 5 7 / 6 3 4 / 5 -2 -3
 * - Ma phương Lo Shu 3x3: 8 1 6 / 3 5 7 / 4 9 2
 * - Nhân ma trận A(2x3): 1 2 3 / 4 5 6 với B(3x2): 7 8 / 9 10 / 11 12
 */

require_once 'matrix_utils.php';

$message = '';
$messageType = '';
$result = '';
$activeTab = 'tab1';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
            $activeTab = $_POST['tab'] ?? 'tab1';
            
            switch ($action) {
                case 'determinant':
                    $result = handleDeterminant();
                    break;
                case 'sort_desc':
                    $result = handleSortDesc();
                    break;
                case 'add_matrices':
                    $result = handleAddMatrices();
                    break;
                case 'sub_matrices':
                    $result = handleSubMatrices();
                    break;
                case 'mul_matrices':
                    $result = handleMulMatrices();
                    break;
                case 'check_magic':
                    $result = handleCheckMagic();
                    break;
                case 'generate_magic':
                    $result = handleGenerateMagic();
                    break;
            }
        }
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'error';
    }
}

function handleDeterminant() {
    global $message, $messageType;
    
    $m = (int)$_POST['m'];
    $n = (int)$_POST['n'];
    $rawMatrix = $_POST['matrix'];
    
    if ($m !== $n) {
        throw new Exception('Chỉ có thể tính định thức cho ma trận vuông');
    }
    
    $matrix = parseMatrix($rawMatrix, $m, $n);
    $validation = validateMatrix($matrix, $m, $n);
    
    if (!$validation['ok']) {
        throw new Exception($validation['msg']);
    }
    
    $det = determinant($matrix);
    $message = 'Định thức tính thành công!';
    $messageType = 'success';
    
    return renderMatrix($matrix) . 
           '<div class="card-panel teal lighten-4"><h6>Định thức = ' . 
           (is_float($det) && floor($det) == $det ? (int)$det : round($det, 6)) . '</h6></div>';
}

function handleSortDesc() {
    global $message, $messageType;
    
    $m = (int)$_POST['m'];
    $n = (int)$_POST['n'];
    $rawMatrix = $_POST['matrix'];
    
    $matrix = parseMatrix($rawMatrix, $m, $n);
    $validation = validateMatrix($matrix, $m, $n);
    
    if (!$validation['ok']) {
        throw new Exception($validation['msg']);
    }
    
    $sortedMatrix = sortMatrixDesc($matrix);
    $message = 'Ma trận đã được sắp xếp giảm dần!';
    $messageType = 'success';
    
    return '<h6>Ma trận gốc:</h6>' . renderMatrix($matrix) . 
           '<h6>Ma trận sau khi sắp xếp giảm dần:</h6>' . renderMatrix($sortedMatrix);
}

function handleAddMatrices() {
    global $message, $messageType;
    
    $mA = (int)$_POST['mA'];
    $nA = (int)$_POST['nA'];
    $rawA = $_POST['matrixA'];
    $mB = (int)$_POST['mB'];
    $nB = (int)$_POST['nB'];
    $rawB = $_POST['matrixB'];
    
    $matrixA = parseMatrix($rawA, $mA, $nA);
    $matrixB = parseMatrix($rawB, $mB, $nB);
    
    $validationA = validateMatrix($matrixA, $mA, $nA);
    $validationB = validateMatrix($matrixB, $mB, $nB);
    
    if (!$validationA['ok']) throw new Exception('Ma trận A: ' . $validationA['msg']);
    if (!$validationB['ok']) throw new Exception('Ma trận B: ' . $validationB['msg']);
    
    $result = add($matrixA, $matrixB);
    $message = 'Tính tổng thành công!';
    $messageType = 'success';
    
    return '<div class="row"><div class="col s4"><h6>Ma trận A:</h6>' . renderMatrix($matrixA) . 
           '</div><div class="col s4"><h6>Ma trận B:</h6>' . renderMatrix($matrixB) . 
           '</div><div class="col s4"><h6>A + B:</h6>' . renderMatrix($result) . '</div></div>';
}

function handleSubMatrices() {
    global $message, $messageType;
    
    $mA = (int)$_POST['mA'];
    $nA = (int)$_POST['nA'];
    $rawA = $_POST['matrixA'];
    $mB = (int)$_POST['mB'];
    $nB = (int)$_POST['nB'];
    $rawB = $_POST['matrixB'];
    
    $matrixA = parseMatrix($rawA, $mA, $nA);
    $matrixB = parseMatrix($rawB, $mB, $nB);
    
    $validationA = validateMatrix($matrixA, $mA, $nA);
    $validationB = validateMatrix($matrixB, $mB, $nB);
    
    if (!$validationA['ok']) throw new Exception('Ma trận A: ' . $validationA['msg']);
    if (!$validationB['ok']) throw new Exception('Ma trận B: ' . $validationB['msg']);
    
    $result = sub($matrixA, $matrixB);
    $message = 'Tính hiệu thành công!';
    $messageType = 'success';
    
    return '<div class="row"><div class="col s4"><h6>Ma trận A:</h6>' . renderMatrix($matrixA) . 
           '</div><div class="col s4"><h6>Ma trận B:</h6>' . renderMatrix($matrixB) . 
           '</div><div class="col s4"><h6>A - B:</h6>' . renderMatrix($result) . '</div></div>';
}

function handleMulMatrices() {
    global $message, $messageType;
    
    $mA = (int)$_POST['mA'];
    $nA = (int)$_POST['nA'];
    $rawA = $_POST['matrixA'];
    $mB = (int)$_POST['mB'];
    $nB = (int)$_POST['nB'];
    $rawB = $_POST['matrixB'];
    
    $matrixA = parseMatrix($rawA, $mA, $nA);
    $matrixB = parseMatrix($rawB, $mB, $nB);
    
    $validationA = validateMatrix($matrixA, $mA, $nA);
    $validationB = validateMatrix($matrixB, $mB, $nB);
    
    if (!$validationA['ok']) throw new Exception('Ma trận A: ' . $validationA['msg']);
    if (!$validationB['ok']) throw new Exception('Ma trận B: ' . $validationB['msg']);
    
    $result = mul($matrixA, $matrixB);
    $message = 'Tính tích thành công!';
    $messageType = 'success';
    
    return '<div class="row"><div class="col s4"><h6>Ma trận A:</h6>' . renderMatrix($matrixA) . 
           '</div><div class="col s4"><h6>Ma trận B:</h6>' . renderMatrix($matrixB) . 
           '</div><div class="col s4"><h6>A × B:</h6>' . renderMatrix($result) . '</div></div>';
}

function handleCheckMagic() {
    global $message, $messageType;
    
    $n = (int)$_POST['n_magic'];
    $rawMatrix = $_POST['magic_matrix'];
    
    $matrix = parseMatrix($rawMatrix, $n, $n);
    $validation = validateMatrix($matrix, $n, $n);
    
    if (!$validation['ok']) {
        throw new Exception($validation['msg']);
    }
    
    $magicResult = isMagicSquare($matrix);
    
    if ($magicResult['ok']) {
        $message = 'Đây là ma phương hợp lệ! Tổng ma phương = ' . (int)$magicResult['magicSum'];
        $messageType = 'success';
    } else {
        $message = 'Không phải ma phương - các tổng hàng/cột/chéo không bằng nhau';
        $messageType = 'warning';
    }
    
    return renderMatrix($matrix) . renderSumsTable($magicResult['sums']);
}

function handleGenerateMagic() {
    global $message, $messageType;
    
    $n = (int)$_POST['n_generate'];
    
    if ($n < 3) {
        throw new Exception('Bậc ma phương phải ≥ 3');
    }
    
    if ($n > 20) {
        throw new Exception('Bậc ma phương quá lớn (tối đa 20)');
    }
    
    $magicSquare = generateMagicSquare($n);
    $magicSum = $n * ($n * $n + 1) / 2;
    
    $message = 'Tạo ma phương bậc ' . $n . ' thành công! Tổng ma phương = ' . (int)$magicSum;
    $messageType = 'success';
    
    return renderMatrix($magicSquare) . 
           '<div class="card-panel green lighten-4"><h6>Tổng ma phương = ' . (int)$magicSum . '</h6></div>';
}

// Giữ lại giá trị form
$formData = [
    'm' => $_POST['m'] ?? '3',
    'n' => $_POST['n'] ?? '3',
    'matrix' => $_POST['matrix'] ?? "2 5 7\n6 3 4\n5 -2 -3",
    'mA' => $_POST['mA'] ?? '2',
    'nA' => $_POST['nA'] ?? '3',
    'matrixA' => $_POST['matrixA'] ?? "1 2 3\n4 5 6",
    'mB' => $_POST['mB'] ?? '3',
    'nB' => $_POST['nB'] ?? '2',
    'matrixB' => $_POST['matrixB'] ?? "7 8\n9 10\n11 12",
    'n_magic' => $_POST['n_magic'] ?? '3',
    'magic_matrix' => $_POST['magic_matrix'] ?? "8 1 6\n3 5 7\n4 9 2",
    'n_generate' => $_POST['n_generate'] ?? '3'
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chuyên đề ma trận - PHP</title>
    
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/style.css" rel="stylesheet">
    
    <style>
        .matrix-table {
            max-width: 100%;
            margin: 10px 0;
        }
        .matrix-table td {
            padding: 8px !important;
            text-align: center;
            font-weight: 500;
            border: 1px solid #e0e0e0;
        }
        .matrix-container {
            margin: 20px 0;
            text-align: center;
        }
        .sums-analysis {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .result-section {
            margin: 30px 0;
            padding: 20px;
            background: #fafafa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header-section {
            text-align: center;
            padding: 40px 0 20px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-bottom: 30px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-section {
            background: white;
            padding: 25px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .input-field textarea {
            min-height: 120px;
        }
        .btn-group {
            margin: 20px 0;
            text-align: center;
        }
        .btn-group .btn {
            margin: 5px;
        }
        .toast {
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-section">
        <div class="container">
            <h2><i class="material-icons left">grid_on</i>Chuyên đề ma trận</h2>
            <h5>PHP thuần - Material Design</h5>
            <p><em>by Kha Minh Đăng</em></p>
        </div>
    </div>

    <div class="container">
        <!-- Tabs -->
        <div class="row">
            <div class="col s12">
                <ul class="tabs">
                    <li class="tab col s3"><a href="#tab1" class="<?= $activeTab === 'tab1' ? 'active' : '' ?>">
                        <i class="material-icons left">looks_one</i>Một ma trận
                    </a></li>
                    <li class="tab col s3"><a href="#tab2" class="<?= $activeTab === 'tab2' ? 'active' : '' ?>">
                        <i class="material-icons left">looks_two</i>Hai ma trận
                    </a></li>
                    <li class="tab col s3"><a href="#tab3" class="<?= $activeTab === 'tab3' ? 'active' : '' ?>">
                        <i class="material-icons left">check_circle</i>Kiểm tra ma phương
                    </a></li>
                    <li class="tab col s3"><a href="#tab4" class="<?= $activeTab === 'tab4' ? 'active' : '' ?>">
                        <i class="material-icons left">auto_fix_high</i>Tạo ma phương
                    </a></li>
                </ul>
            </div>
        </div>

        <!-- Tab 1: Một ma trận -->
        <div id="tab1" class="tab-content <?= $activeTab === 'tab1' ? 'active' : '' ?>">
            <div class="form-section">
                <h5><i class="material-icons left">grid_on</i>Xử lý một ma trận</h5>
                <form method="POST" action="">
                    <input type="hidden" name="tab" value="tab1">
                    
                    <div class="row">
                        <div class="input-field col s6">
                            <input type="number" id="m" name="m" value="<?= htmlspecialchars($formData['m']) ?>" min="1" max="20" required>
                            <label for="m">Số hàng (m)</label>
                        </div>
                        <div class="input-field col s6">
                            <input type="number" id="n" name="n" value="<?= htmlspecialchars($formData['n']) ?>" min="1" max="20" required>
                            <label for="n">Số cột (n)</label>
                        </div>
                    </div>
                    
                    <div class="input-field">
                        <textarea id="matrix" name="matrix" class="materialize-textarea" required><?= htmlspecialchars($formData['matrix']) ?></textarea>
                        <label for="matrix">Nhập ma trận (mỗi hàng một dòng, các số cách nhau bởi dấu cách)</label>
                        <span class="helper-text">Ví dụ: 2 5 7</span>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="action" value="determinant" class="btn waves-effect waves-light blue">
                            <i class="material-icons left">calculate</i>Tính định thức
                        </button>
                        <button type="submit" name="action" value="sort_desc" class="btn waves-effect waves-light green">
                            <i class="material-icons left">sort</i>Sắp xếp giảm dần
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab 2: Hai ma trận -->
        <div id="tab2" class="tab-content <?= $activeTab === 'tab2' ? 'active' : '' ?>">
            <div class="form-section">
                <h5><i class="material-icons left">view_module</i>Phép toán hai ma trận</h5>
                <form method="POST" action="">
                    <input type="hidden" name="tab" value="tab2">
                    
                    <div class="row">
                        <div class="col s6">
                            <h6>Ma trận A</h6>
                            <div class="row">
                                <div class="input-field col s6">
                                    <input type="number" name="mA" value="<?= htmlspecialchars($formData['mA']) ?>" min="1" max="20" required>
                                    <label>Hàng A (m)</label>
                                </div>
                                <div class="input-field col s6">
                                    <input type="number" name="nA" value="<?= htmlspecialchars($formData['nA']) ?>" min="1" max="20" required>
                                    <label>Cột A (n)</label>
                                </div>
                            </div>
                            <div class="input-field">
                                <textarea name="matrixA" class="materialize-textarea" required><?= htmlspecialchars($formData['matrixA']) ?></textarea>
                                <label>Ma trận A</label>
                            </div>
                        </div>
                        
                        <div class="col s6">
                            <h6>Ma trận B</h6>
                            <div class="row">
                                <div class="input-field col s6">
                                    <input type="number" name="mB" value="<?= htmlspecialchars($formData['mB']) ?>" min="1" max="20" required>
                                    <label>Hàng B (p)</label>
                                </div>
                                <div class="input-field col s6">
                                    <input type="number" name="nB" value="<?= htmlspecialchars($formData['nB']) ?>" min="1" max="20" required>
                                    <label>Cột B (q)</label>
                                </div>
                            </div>
                            <div class="input-field">
                                <textarea name="matrixB" class="materialize-textarea" required><?= htmlspecialchars($formData['matrixB']) ?></textarea>
                                <label>Ma trận B</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="action" value="add_matrices" class="btn waves-effect waves-light blue">
                            <i class="material-icons left">add</i>A + B
                        </button>
                        <button type="submit" name="action" value="sub_matrices" class="btn waves-effect waves-light orange">
                            <i class="material-icons left">remove</i>A - B
                        </button>
                        <button type="submit" name="action" value="mul_matrices" class="btn waves-effect waves-light purple">
                            <i class="material-icons left">close</i>A × B
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab 3: Kiểm tra ma phương -->
        <div id="tab3" class="tab-content <?= $activeTab === 'tab3' ? 'active' : '' ?>">
            <div class="form-section">
                <h5><i class="material-icons left">verified</i>Kiểm tra ma phương</h5>
                <form method="POST" action="">
                    <input type="hidden" name="tab" value="tab3">
                    
                    <div class="input-field col s12">
                        <input type="number" name="n_magic" value="<?= htmlspecialchars($formData['n_magic']) ?>" min="1" max="20" required>
                        <label>Bậc ma phương (n)</label>
                    </div>
                    
                    <div class="input-field">
                        <textarea name="magic_matrix" class="materialize-textarea" required><?= htmlspecialchars($formData['magic_matrix']) ?></textarea>
                        <label>Nhập ma trận n×n</label>
                        <span class="helper-text">Ma phương Lo Shu 3×3: 8 1 6 / 3 5 7 / 4 9 2</span>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="action" value="check_magic" class="btn waves-effect waves-light teal">
                            <i class="material-icons left">search</i>Kiểm tra ma phương
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab 4: Tạo ma phương -->
        <div id="tab4" class="tab-content <?= $activeTab === 'tab4' ? 'active' : '' ?>">
            <div class="form-section">
                <h5><i class="material-icons left">auto_awesome</i>Tạo ma phương bậc n</h5>
                <form method="POST" action="">
                    <input type="hidden" name="tab" value="tab4">
                    
                    <div class="input-field col s12">
                        <input type="number" name="n_generate" value="<?= htmlspecialchars($formData['n_generate']) ?>" min="3" max="20" required>
                        <label>Bậc ma phương (n ≥ 3)</label>
                        <span class="helper-text">Nhập số từ 3 đến 20</span>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="action" value="generate_magic" class="btn waves-effect waves-light indigo">
                            <i class="material-icons left">create</i>Tạo ma phương
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kết quả -->
        <?php if ($result): ?>
        <div class="result-section">
            <h5><i class="material-icons left">assessment</i>Kết quả</h5>
            <?= $result ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Khởi tạo tabs
            var tabs = document.querySelectorAll('.tabs');
            M.Tabs.init(tabs);
            
            // Khởi tạo form labels
            M.updateTextFields();
            
            // Hiển thị thông báo
            <?php if ($message): ?>
            var toastClass = '<?= $messageType === "error" ? "red" : ($messageType === "warning" ? "orange" : "green") ?>';
            M.toast({
                html: '<i class="material-icons left">info</i><?= addslashes($message) ?>',
                classes: toastClass + ' white-text',
                displayLength: 4000
            });
            <?php endif; ?>
            
            // Kích hoạt tab đúng sau submit
            var activeTabId = '<?= $activeTab ?>';
            var tabInstance = M.Tabs.getInstance(document.querySelector('.tabs'));
            if (tabInstance) {
                tabInstance.select(activeTabId);
            }
        });
    </script>
</body>
</html>
