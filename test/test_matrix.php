<?php
require_once __DIR__ . '/../matrix_utils.php';

function assertEqual($expected, $actual, $msg = '') {
    // Xử lý so sánh số (int vs float)
    if (is_numeric($expected) && is_numeric($actual)) {
        if (abs($expected - $actual) < 1e-9) {
            echo "✅ PASS: $msg\n";
            return;
        }
    } elseif ($expected === $actual) {
        echo "✅ PASS: $msg\n";
        return;
    }
    
    echo "❌ FAIL: $msg\n";
    echo "   Expected: "; var_export($expected); echo "\n";
    echo "   Actual  : "; var_export($actual); echo "\n";
}

// 1. parseMatrix + validateMatrix
$raw = "2 5 7\n6 3 4\n5 -2 -3";
$A = parseMatrix($raw, 3, 3);
$v = validateMatrix($A, 3, 3);
assertEqual(true, $v['ok'], "validateMatrix 3x3 hợp lệ");

// 2. determinant
$det = determinant($A);
// Ma trận [ [2,5,7], [6,3,4], [5,-2,-3] ] có định thức = -1
assertEqual(-1, round($det), "determinant 3x3");

// 3. sortMatrixDesc
$sorted = sortMatrixDesc($A);
assertEqual(7, $sorted[0][0], "sortMatrixDesc: phần tử đầu tiên phải max");

// 4. add/sub
$B = [[1,1,1],[1,1,1],[1,1,1]];
$sum = add($A, $B);
$diff = sub($A, $B);
assertEqual(3, $sum[0][0], "A+B phần tử (0,0)");
assertEqual(2, $diff[1][1], "A-B phần tử (1,1) = 3-1 = 2");

// 5. mul
$C = [[1,2,3],[4,5,6]];
$D = [[7,8],[9,10],[11,12]];
$mul = mul($C, $D);
// Expected: [[58,64],[139,154]]
assertEqual(58, $mul[0][0], "A×B (0,0)");
assertEqual(154, $mul[1][1], "A×B (1,1)");

// 6. isMagicSquare (Lo Shu)
$LoShu = [[8,1,6],[3,5,7],[4,9,2]];
$res = isMagicSquare($LoShu);
assertEqual(true, $res['ok'], "Lo Shu là ma phương");
assertEqual(15, $res['magicSum'], "Magic Sum của Lo Shu = 15");

// 7. generateMagicSquare odd
$magic3 = generateMagicSquare(3);
$res3 = isMagicSquare($magic3);
assertEqual(true, $res3['ok'], "generateMagicSquare(3) ra ma phương");

// 8. generateMagicSquare doubly even (4)
$magic4 = generateMagicSquare(4);
$res4 = isMagicSquare($magic4);
assertEqual(true, $res4['ok'], "generateMagicSquare(4) ra ma phương");

// 9. generateMagicSquare singly even (6)
$magic6 = generateMagicSquare(6);
$res6 = isMagicSquare($magic6);
assertEqual(true, $res6['ok'], "generateMagicSquare(6) ra ma phương");

echo "\nAll tests finished.\n";
