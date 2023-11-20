<?php

// return true, exist true:  array(
//             first arg         0 => exist / 1 => failue,
//             second arg        data       ; 0 => not exist / 1 => error
//                           )
// return true, exist false: array(
//             first arg         0 => success / 1 => failue,
//             second arg        data         ; error info
//                           )
// return false:             void

function pdoDo($str, $ary, $return = false, $exist = false) {
    global $sub;
	try {
		$pdo = new PDO(
			'mysql:dbname=' . $sub . ';host=localhost',
			'user',
			'********************'
		);
		$stmt = $pdo->prepare($str);
		$res = $stmt->execute($ary);
		$pdo = null;
		if($res) {
			if($return) {
				$data = $stmt->fetch();
				if($exist) {
					if($data) {
						return array(0, $data);
					}else {
						return array(1, 0);
					}
				}else {
					return array(0, $data);
				}
			}else {
				return;
			}
		}else {
			throw new Exception($stmt->errorInfo()[2]);
		}
	}catch (PDOException $e) {
		$pdo = null;
		if($return) {
			return array(1, $stmt->errorInfo()[2]);
		}else {
			error('PDO failue', $e->getMessage());
		}
	}
}

// available orders remaining
// not handed paypal
// not handed invite
// reserved (not handed)

function countOrder() {
    $order = pdoDo(
        'select sum(quantity) from main;',
        [],
        true
    );
    $wait = pdoDo(
        'select sum(quantity) from main where status is null;',
        [],
        true
    );
    $count = pdoDo(
        'select number from count where name = "count";',
        [],
        true
    );
    $invit = pdoDo(
        'select number from count where name = "invit";',
        [],
        true
    );
    $total = pdoDo(
        'select number from count where name = "total";',
        [],
        true
    );
    if($wait[1][0]) {
        $wait = $wait[1][0];
    }else {
        $wait = 0;
    }
    $res = array(
        'paypal' => $order[1][0], // total orders
        'wait' => $wait, // handed orders
        'inperson' => $count[1][0],
        'invited' => $invit[1][0],
        'available' => $total[1][0] - $order[1][0] - $count[1][0]
    );
    return $res;
}

function updateCount($i, $j, $k = null) {
    if($i) {
        if(is_numeric($i)) {
            pdoDo(
                'update count set number = number + ? where name = "count";',
                [$i]
            );
        }
    }
    if($j) {
        if(is_numeric($j)) {
            pdoDo(
                'update count set number = number + ? where name = "invit";',
                [$j]
            );
        }
    }
    if($k) {
        if(is_numeric($k)) {
            pdoDo(
                'update count set number = number + ? where name = "total";',
                [$k]
            );
        }
    }
}
