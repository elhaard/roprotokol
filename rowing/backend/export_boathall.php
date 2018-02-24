<?php
include("inc/common.php");
header('Content-type: application/json');

// include("inc/verify_user.php");

$data = [ 'aisles' => [] ];

$dbres = $rodb->query("SELECT
     s.id as slot_id,
     s.order as slot_order,
     s.size as slot_size,
     c.text as conf_text,
     c.id as conf_id,
     r.id as row_id,
     r.level as row_level,
     r.name as row_name,
     r.order as row_order,
     si.id as side_id,
     si.name as side_name,
     si.order as side_order,
     a.id as aisle_id,
     a.name as aisle_name,
     a.order as aisle_order,
     a.location as location,
     GROUP_CONCAT(bt.boatType ORDER by bt.boatType ASC SEPARATOR ',') as boatTypes
     FROM PlacementSlot s
     JOIN PlacementRowConfiguration c ON s.configuration = c.id
     JOIN PlacementRow r ON c.row = r.id
     JOIN PlacementSide si ON r.side = si.id
     JOIN PlacementAisle a ON si.aisle = a.id
     LEFT JOIN Placement_BoatType bt ON bt.slot = s.id
     GROUP BY s.id, c.id, r.id, si.id, a.id
     ORDER BY a.order, a.id, si.order, si.id, r.order, r.id, c.id, s.order, s.id");

if (!$dbres) {
  die("Could not prepare SQL: " . $rodb->error);
}

$aisle = null;
$side = null;
$row = null;
$conf = null;
$slot = null;

function toInt ($i) {
  return (int) $i;
}

while ($r = $dbres->fetch_assoc()) {
  if (is_null($aisle) || $aisle['id'] != $r['aisle_id']) {
    $aisle = [ 'id' => $r['aisle_id'],
               'name' => $r['aisle_name'],
               'order' => (int) $r['aisle_order'],
               'location' => $r['location'],
               'sides' => []
             ];
    $data['aisles'][] = $aisle;
    $aisle_no = count($data['aisles']) -1;

  }
  if (is_null($side) || $side['id'] != $r['side_id']) {
    $side = [ 'id' => (int) $r['side_id'],
              'name' => $r['side_name'],
              'order' => (int) $r['side_order'],
              'rows'  => []
            ];
    $data['aisles'][$aisle_no]['sides'][] = $side;  // Why oh why does PHP not have proper references?
    $side_no = count($data['aisles'][$aisle_no]['sides']) -1;
  }
  if (is_null($row) || $row['id'] != $r['row_id']) {
    $row = [ 'id' => (int) $r['row_id'],
            'name' => $r['row_name'],
            'order' => (int) $r['row_order'],
            'level' => (int) $r['row_level'],
            'configurations'  => []
          ];
    $data['aisles'][$aisle_no]['sides'][$side_no]['rows'][] = $row;
    $row_no = count($data['aisles'][$aisle_no]['sides'][$side_no]['rows']) -1;
  }
  if (is_null($conf) || $conf['id'] != $r['conf_id']) {
    $conf = [ 'id' => (int) $r['conf_id'],
              'text' => $r['conf_text'],
              'slots'  => []
            ];
    $data['aisles'][$aisle_no]['sides'][$side_no]['rows'][$row_no]['configurations'][] = $conf;
    $conf_no = count($data['aisles'][$aisle_no]['sides'][$side_no]['rows'][$row_no]['configurations']) - 1;
  }
  if (is_null($slot) || $slot['id'] != $r['slot_id']) {
    $bt = null;
    if ($r['boatTypes']) {
      $bt = array_map("toInt", explode(',', $r['boatTypes']));
    }
    $slot = [ 'id' => (int) $r['slot_id'],
              'size' => (int) $r['slot_size'],
              'order' => (int) $r['side_order'],
              'boatTypes'  => $bt
            ];
    $data['aisles'][$aisle_no]['sides'][$side_no]['rows'][$row_no]['configurations'][$conf_no]['slots'][] = $slot;
  }
}

echo json_encode($data, JSON_PRETTY_PRINT);
$rodb->close();


?>
