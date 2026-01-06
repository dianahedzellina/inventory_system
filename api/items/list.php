<?php
header("Content-Type: application/json");
include '../../database.php';

$res = $conn->query("SELECT item_id, item_name, category, quantity FROM items ORDER BY item_name");

$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = [
        "item_id"   => (int)$r["item_id"],
        "item_name" => $r["item_name"],
        "category"  => $r["category"],
        "quantity"  => (int)$r["quantity"],
        "status"    => ((int)$r["quantity"] > 0 ? "available" : "unavailable")
    ];
}
echo json_encode($out);
