<?php
/**
 * Matrix Operations Utility Functions
 * Chuyên đề ma trận - PHP thuần
 * by Kha Minh Đăng
 */

const EPS = 1e-9; // Epsilon cho so sánh số thực

/**
 * Phân tích chuỗi nhập thành ma trận 2D
 * @param string $raw Chuỗi nhập từ người dùng
 * @param int $m Số hàng
 * @param int $n Số cột
 * @return array Ma trận 2D
 */
function parseMatrix(string $raw, int $m, int $n): array {
    $lines = explode("\n", trim($raw));
    $matrix = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Tách các số bằng space, tab, dấu phẩy
        $numbers = preg_split('/[\s,]+/', $line);
        $row = [];
        
        foreach ($numbers as $num) {
            $num = trim($num);
            if ($num !== '') {
                $row[] = (float)$num;
            }
        }
        
        if (!empty($row)) {
            $matrix[] = $row;
        }
    }
    
    return $matrix;
}

/**
 * Kiểm tra tính hợp lệ của ma trận
 * @param array $A Ma trận cần kiểm tra
 * @param int $m Số hàng mong muốn
 * @param int $n Số cột mong muốn
 * @return array ['ok' => bool, 'msg' => string]
 */
function validateMatrix(array $A, int $m, int $n): array {
    if (count($A) !== $m) {
        return ['ok' => false, 'msg' => "Cần đúng $m hàng, nhận được " . count($A) . " hàng"];
    }
    
    for ($i = 0; $i < $m; $i++) {
        if (!is_array($A[$i])) {
            return ['ok' => false, 'msg' => "Hàng " . ($i + 1) . " không hợp lệ"];
        }
        
        if (count($A[$i]) !== $n) {
            return ['ok' => false, 'msg' => "Hàng " . ($i + 1) . " cần $n cột, nhận được " . count($A[$i]) . " cột"];
        }
        
        for ($j = 0; $j < $n; $j++) {
            if (!is_numeric($A[$i][$j])) {
                return ['ok' => false, 'msg' => "Phần tử ở hàng " . ($i + 1) . ", cột " . ($j + 1) . " không phải là số"];
            }
        }
    }
    
    return ['ok' => true, 'msg' => 'Ma trận hợp lệ'];
}

/**
 * Lấy kích thước ma trận
 * @param array $A Ma trận
 * @return array ['m' => int, 'n' => int]
 */
function shape(array $A): array {
    return ['m' => count($A), 'n' => count($A[0] ?? [])];
}

/**
 * Tạo bản sao sâu của ma trận
 * @param array $A Ma trận gốc
 * @return array Ma trận sao chép
 */
function deepCopy(array $A): array {
    $copy = [];
    for ($i = 0; $i < count($A); $i++) {
        $copy[$i] = [];
        for ($j = 0; $j < count($A[$i]); $j++) {
            $copy[$i][$j] = $A[$i][$j];
        }
    }
    return $copy;
}

/**
 * Render ma trận thành HTML table với Material Design
 * @param array $A Ma trận
 * @return string HTML table
 */
function renderMatrix(array $A): string {
    if (empty($A)) return '<p>Ma trận rỗng</p>';
    
    $shape = shape($A);
    $html = '<div class="matrix-container">';
    $html .= '<h6>Ma trận ' . $shape['m'] . '×' . $shape['n'] . '</h6>';
    $html .= '<table class="matrix-table striped centered responsive-table">';
    
    foreach ($A as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $value = is_float($cell) && floor($cell) == $cell ? (int)$cell : round($cell, 4);
            $html .= '<td>' . htmlspecialchars($value) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</table></div>';
    return $html;
}

/**
 * Tính định thức ma trận vuông bằng khử Gauss với partial pivoting
 * @param array $A Ma trận vuông n×n
 * @return float Định thức
 */
function determinant(array $A): float {
    $n = count($A);
    if ($n === 0 || count($A[0]) !== $n) {
        throw new InvalidArgumentException('Ma trận phải là ma trận vuông');
    }
    
    // Tạo bản sao để không thay đổi ma trận gốc
    $matrix = deepCopy($A);
    $det = 1.0;
    
    for ($i = 0; $i < $n; $i++) {
        // Tìm phần tử trụ (pivot) lớn nhất
        $maxRow = $i;
        for ($k = $i + 1; $k < $n; $k++) {
            if (abs($matrix[$k][$i]) > abs($matrix[$maxRow][$i])) {
                $maxRow = $k;
            }
        }
        
        // Đổi hàng nếu cần
        if ($maxRow !== $i) {
            $temp = $matrix[$i];
            $matrix[$i] = $matrix[$maxRow];
            $matrix[$maxRow] = $temp;
            $det *= -1; // Đổi dấu định thức
        }
        
        // Kiểm tra ma trận suy biến
        if (abs($matrix[$i][$i]) < EPS) {
            return 0.0;
        }
        
        $det *= $matrix[$i][$i];
        
        // Khử Gauss
        for ($k = $i + 1; $k < $n; $k++) {
            $factor = $matrix[$k][$i] / $matrix[$i][$i];
            for ($j = $i; $j < $n; $j++) {
                $matrix[$k][$j] -= $factor * $matrix[$i][$j];
            }
        }
    }
    
    return abs($det) < EPS ? 0.0 : $det;
}

/**
 * Sắp xếp ma trận theo thứ tự giảm dần (trên→dưới, trái→phải)
 * @param array $A Ma trận gốc
 * @return array Ma trận đã sắp xếp
 */
function sortMatrixDesc(array $A): array {
    $shape = shape($A);
    
    // Flatten thành mảng 1D
    $elements = [];
    for ($i = 0; $i < $shape['m']; $i++) {
        for ($j = 0; $j < $shape['n']; $j++) {
            $elements[] = $A[$i][$j];
        }
    }
    
    // Sắp xếp giảm dần
    rsort($elements);
    
    // Reshape lại thành ma trận
    $result = [];
    $index = 0;
    for ($i = 0; $i < $shape['m']; $i++) {
        $result[$i] = [];
        for ($j = 0; $j < $shape['n']; $j++) {
            $result[$i][$j] = $elements[$index++];
        }
    }
    
    return $result;
}

/**
 * Cộng hai ma trận
 * @param array $A Ma trận thứ nhất
 * @param array $B Ma trận thứ hai
 * @return array Ma trận tổng
 */
function add(array $A, array $B): array {
    $shapeA = shape($A);
    $shapeB = shape($B);
    
    if ($shapeA['m'] !== $shapeB['m'] || $shapeA['n'] !== $shapeB['n']) {
        throw new InvalidArgumentException('Hai ma trận phải có cùng kích thước để cộng');
    }
    
    $result = [];
    for ($i = 0; $i < $shapeA['m']; $i++) {
        $result[$i] = [];
        for ($j = 0; $j < $shapeA['n']; $j++) {
            $result[$i][$j] = $A[$i][$j] + $B[$i][$j];
        }
    }
    
    return $result;
}

/**
 * Trừ hai ma trận
 * @param array $A Ma trận thứ nhất
 * @param array $B Ma trận thứ hai
 * @return array Ma trận hiệu
 */
function sub(array $A, array $B): array {
    $shapeA = shape($A);
    $shapeB = shape($B);
    
    if ($shapeA['m'] !== $shapeB['m'] || $shapeA['n'] !== $shapeB['n']) {
        throw new InvalidArgumentException('Hai ma trận phải có cùng kích thước để trừ');
    }
    
    $result = [];
    for ($i = 0; $i < $shapeA['m']; $i++) {
        $result[$i] = [];
        for ($j = 0; $j < $shapeA['n']; $j++) {
            $result[$i][$j] = $A[$i][$j] - $B[$i][$j];
        }
    }
    
    return $result;
}

/**
 * Nhân hai ma trận
 * @param array $A Ma trận m×n
 * @param array $B Ma trận n×p
 * @return array Ma trận tích m×p
 */
function mul(array $A, array $B): array {
    $shapeA = shape($A);
    $shapeB = shape($B);
    
    if ($shapeA['n'] !== $shapeB['m']) {
        throw new InvalidArgumentException('Số cột của ma trận A phải bằng số hàng của ma trận B');
    }
    
    $m = $shapeA['m'];
    $n = $shapeA['n'];
    $p = $shapeB['n'];
    
    $result = [];
    for ($i = 0; $i < $m; $i++) {
        $result[$i] = [];
        for ($j = 0; $j < $p; $j++) {
            $sum = 0;
            for ($k = 0; $k < $n; $k++) {
                $sum += $A[$i][$k] * $B[$k][$j];
            }
            $result[$i][$j] = $sum;
        }
    }
    
    return $result;
}

/**
 * Kiểm tra ma phương
 * @param array $A Ma trận vuông n×n
 * @return array ['ok' => bool, 'magicSum' => float, 'sums' => array]
 */
function isMagicSquare(array $A): array {
    $n = count($A);
    if ($n === 0 || count($A[0]) !== $n) {
        return ['ok' => false, 'magicSum' => 0, 'sums' => []];
    }
    
    $sums = [
        'rows' => [],
        'cols' => [],
        'diag' => 0,
        'anti' => 0
    ];
    
    // Tính tổng các hàng
    for ($i = 0; $i < $n; $i++) {
        $rowSum = 0;
        for ($j = 0; $j < $n; $j++) {
            $rowSum += $A[$i][$j];
        }
        $sums['rows'][] = $rowSum;
    }
    
    // Tính tổng các cột
    for ($j = 0; $j < $n; $j++) {
        $colSum = 0;
        for ($i = 0; $i < $n; $i++) {
            $colSum += $A[$i][$j];
        }
        $sums['cols'][] = $colSum;
    }
    
    // Tính tổng đường chéo chính
    for ($i = 0; $i < $n; $i++) {
        $sums['diag'] += $A[$i][$i];
    }
    
    // Tính tổng đường chéo phụ
    for ($i = 0; $i < $n; $i++) {
        $sums['anti'] += $A[$i][$n - 1 - $i];
    }
    
    // Kiểm tra tất cả tổng có bằng nhau không
    $magicSum = $sums['rows'][0];
    $isMagic = true;
    
    // Kiểm tra tổng các hàng
    foreach ($sums['rows'] as $sum) {
        if (abs($sum - $magicSum) > EPS) {
            $isMagic = false;
            break;
        }
    }
    
    // Kiểm tra tổng các cột
    if ($isMagic) {
        foreach ($sums['cols'] as $sum) {
            if (abs($sum - $magicSum) > EPS) {
                $isMagic = false;
                break;
            }
        }
    }
    
    // Kiểm tra đường chéo
    if ($isMagic) {
        if (abs($sums['diag'] - $magicSum) > EPS || abs($sums['anti'] - $magicSum) > EPS) {
            $isMagic = false;
        }
    }
    
    return [
        'ok' => $isMagic,
        'magicSum' => $isMagic ? $magicSum : 0,
        'sums' => $sums
    ];
}

/**
 * Tạo ma phương bậc n
 * @param int $n Bậc của ma phương (n ≥ 3)
 * @return array Ma trận ma phương n×n
 */
function generateMagicSquare(int $n): array {
    if ($n < 3) {
        throw new InvalidArgumentException('Bậc ma phương phải ≥ 3');
    }
    
    if ($n % 2 === 1) {
        // Ma phương bậc lẻ - Thuật toán Siamese
        return generateOddMagicSquare($n);
    } elseif ($n % 4 === 0) {
        // Ma phương bậc chẵn bội 4 - Doubly Even
        return generateDoublyEvenMagicSquare($n);
    } else {
        // Ma phương bậc chẵn không bội 4 - Singly Even
        return generateSinglyEvenMagicSquare($n);
    }
}

/**
 * Tạo ma phương bậc lẻ bằng thuật toán Siamese
 */
function generateOddMagicSquare(int $n): array {
    $magic = array_fill(0, $n, array_fill(0, $n, 0));
    
    // Bắt đầu ở hàng 0, cột giữa
    $i = 0;
    $j = intval($n / 2);
    
    for ($num = 1; $num <= $n * $n; $num++) {
        $magic[$i][$j] = $num;
        
        // Tính vị trí tiếp theo: lên 1 hàng, phải 1 cột
        $next_i = ($i - 1 + $n) % $n;
        $next_j = ($j + 1) % $n;
        
        // Nếu ô đã được điền, lùi xuống 1 hàng từ vị trí hiện tại
        if ($magic[$next_i][$next_j] !== 0) {
            $i = ($i + 1) % $n;
        } else {
            $i = $next_i;
            $j = $next_j;
        }
    }
    
    return $magic;
}

/**
 * Tạo ma phương bậc chẵn bội 4 (Doubly Even)
 */
function generateDoublyEvenMagicSquare(int $n): array {
    $magic = [];
    
    // Điền số từ 1 đến n²
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $magic[$i][$j] = $i * $n + $j + 1;
        }
    }
    
    // Đảo ngược các phần tử theo pattern
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $block_i = intval($i / 4);
            $block_j = intval($j / 4);
            $sub_i = $i % 4;
            $sub_j = $j % 4;
            
            // Đảo nếu ở vị trí đường chéo trong block 4x4
            if (($sub_i === $sub_j) || ($sub_i + $sub_j === 3)) {
                $magic[$i][$j] = $n * $n + 1 - $magic[$i][$j];
            }
        }
    }
    
    return $magic;
}

/**
 * Tạo ma phương bậc chẵn không bội 4 (Singly Even)
 */
function generateSinglyEvenMagicSquare(int $n): array {
    $p = $n / 2;
    $magic = array_fill(0, $n, array_fill(0, $n, 0));
    
    // Tạo ma phương bậc lẻ p×p
    $oddMagic = generateOddMagicSquare($p);
    
    // Tạo 4 quadrant
    for ($i = 0; $i < $p; $i++) {
        for ($j = 0; $j < $p; $j++) {
            $val = $oddMagic[$i][$j];
            $magic[$i][$j] = $val;                    // A
            $magic[$i][$j + $p] = $val + 2 * $p * $p; // B
            $magic[$i + $p][$j] = $val + 3 * $p * $p; // C
            $magic[$i + $p][$j + $p] = $val + $p * $p; // D
        }
    }
    
    // Hoán đổi các cột theo quy tắc LUX
    $k = ($p - 1) / 2;
    
    // Hoán đổi k cột đầu tiên giữa A và C
    for ($j = 0; $j < $k; $j++) {
        for ($i = 0; $i < $p; $i++) {
            $temp = $magic[$i][$j];
            $magic[$i][$j] = $magic[$i + $p][$j];
            $magic[$i + $p][$j] = $temp;
        }
    }
    
    // Hoán đổi k-1 cột cuối cùng giữa B và D
    for ($j = $p + 1; $j < $n; $j++) {
        for ($i = 0; $i < $p; $i++) {
            $temp = $magic[$i][$j];
            $magic[$i][$j] = $magic[$i + $p][$j];
            $magic[$i + $p][$j] = $temp;
        }
    }
    
    // Hoán đổi đặc biệt cho hàng giữa
    $middle = intval($p / 2);
    $temp = $magic[$middle][0];
    $magic[$middle][0] = $magic[$middle + $p][0];
    $magic[$middle + $p][0] = $temp;
    
    $temp = $magic[$middle][$k];
    $magic[$middle][$k] = $magic[$middle + $p][$k];
    $magic[$middle + $p][$k] = $temp;
    
    return $magic;
}

/**
 * Render bảng phân tích tổng cho ma phương
 * @param array $sums Mảng tổng từ isMagicSquare
 * @return string HTML table
 */
function renderSumsTable(array $sums): string {
    $html = '<div class="sums-analysis">';
    $html .= '<h6>Phân tích tổng</h6>';
    $html .= '<table class="striped">';
    
    // Tổng các hàng
    $html .= '<tr><td><strong>Tổng các hàng:</strong></td><td>';
    $html .= implode(', ', array_map(function($sum) {
        return is_float($sum) && floor($sum) == $sum ? (int)$sum : round($sum, 4);
    }, $sums['rows']));
    $html .= '</td></tr>';
    
    // Tổng các cột
    $html .= '<tr><td><strong>Tổng các cột:</strong></td><td>';
    $html .= implode(', ', array_map(function($sum) {
        return is_float($sum) && floor($sum) == $sum ? (int)$sum : round($sum, 4);
    }, $sums['cols']));
    $html .= '</td></tr>';
    
    // Đường chéo chính
    $diagSum = is_float($sums['diag']) && floor($sums['diag']) == $sums['diag'] ? (int)$sums['diag'] : round($sums['diag'], 4);
    $html .= '<tr><td><strong>Đường chéo chính:</strong></td><td>' . $diagSum . '</td></tr>';
    
    // Đường chéo phụ
    $antiSum = is_float($sums['anti']) && floor($sums['anti']) == $sums['anti'] ? (int)$sums['anti'] : round($sums['anti'], 4);
    $html .= '<tr><td><strong>Đường chéo phụ:</strong></td><td>' . $antiSum . '</td></tr>';
    
    $html .= '</table></div>';
    return $html;
}
?>
