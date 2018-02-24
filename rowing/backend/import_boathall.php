<?php
include("inc/common.php");
header('Content-type: application/json');

// include("inc/verify_user.php");

$error=null;
$res=array("status" => "ok");
$log= [];

$boathall = json_decode(file_get_contents('boathall.json'), true);

if (! (isset($boathall) && isset($boathall['aisles']))) {
  $error = 'Could not read boathall file';
}

$rodb->autocommit(false);
if ($rodb->begin_transaction()) {
	$log[] = "Started transaction";
} else {
	$error = "Could not start transaction: " . $rodb->error;
}


if (is_null($error)) {
  $rodb->query("DELETE FROM Placement_BoatType");
  $rodb->query("DELETE FROM PlacementSlot");
  $rodb->query("DELETE FROM PlacementRowConfiguration");
  $rodb->query("DELETE FROM PlacementRow");
  $rodb->query("DELETE FROM PlacementSide");
  $rodb->query("DELETE FROM PlacementAisle");
  $log[] = "Deleted previous content";

  $insert_aisle = $rodb->prepare("INSERT INTO PlacementAisle (location, name, `order`) VALUES (?, ?, ?)");
  $log[] = "Prepare insert aisle " . ($insert_aisle ? "Succeded" : "Failed " . $rodb->error);
  $insert_side  = $rodb->prepare("INSERT INTO PlacementSide (aisle, name, `order`) VALUES (?, ?, ?)");
  $log[] = "Prepare insert side " . ($insert_side ? "Succeded" : "Failed " . $rodb->error);
  $insert_row   = $rodb->prepare("INSERT INTO PlacementRow (level, side, name, `order`) VALUES (?, ?, ?, ?)");
  $log[] = "Prepare insert row " . ($insert_row ? "Succeded" : "Failed " . $rodb->error);
  $insert_conf  = $rodb->prepare("INSERT INTO PlacementRowConfiguration (row, `text`) VALUES (?, ?)");
  $log[] = "Prepare insert config " . ($insert_conf ? "Succeded" : "Failed " . $rodb->error);
  $insert_slot  = $rodb->prepare("INSERT INTO PlacementSlot (configuration, size, `order`) VALUES (?, ?, ?)");
  $log[] = "Prepare insert slot " . ($insert_slot ? "Succeded" : "Failed " . $rodb->error);
  $insert_type  = $rodb->prepare("INSERT INTO Placement_BoatType (slot, boatType) VALUES (?, ?)");
  $log[] = "Prepare insert type " . ($insert_type ? "Succeded" : "Failed " . $rodb->error);

  if (! ($insert_aisle && $insert_side && $insert_row && $insert_conf && $insert_slot && $insert_type)) {
    $error = "Could not prepare SQL";
  }
}

if (is_null($error)) {
  foreach ($boathall['aisles'] as $aisle) {
    $insert_aisle->bind_param('ssi', $aisle['location'], $aisle['name'], $aisle['order']);
    if (! $insert_aisle->execute()) {
      $error = "Could not insert aisle: " . $rodb->error;
      break 1;
    }
    $aisle['id'] = $rodb->insert_id;
    $log[] = "Inserted " . $aisle['name'] . " as ID " . $aisle['id'];

    foreach ($aisle['sides'] as $side) {
      $insert_side->bind_param('isi', $aisle['id'], $side['name'], $side['order']);
      if (! $insert_side->execute()) {
        $error = "Could not insert side: " . $rodb->error;
        break 2;
      }
      $side['id'] = $rodb->insert_id;

      foreach ($side['rows'] as $row) {
        $insert_row->bind_param('iisi', $row['level'], $side['id'], $row['name'], $row['order']);
        if (! $insert_row->execute()) {
          $error = "Could not insert row: " . $rodb->error;
          break 3;
        }
        $row['id'] = $rodb->insert_id;

        $log[] = "  Configurations: " . count($row['configurations']);

        foreach ($row['configurations'] as $config) {
          $txt = '';
          $insert_conf->bind_param('is', $row['id'], $txt);
          if (! $insert_conf->execute()) {
            $error = "Could not insert rowConfiguration: " . $rodb->error;
            break 4;
          }
          $conf_id = $rodb->insert_id;

          $slot_order = 0;
          foreach ($config as $slot) {
            $slot_order++;
            $sz = 10;
            $insert_slot->bind_param('iii', $conf_id, $sz, $slot_order);
            if (! $insert_slot->execute()) {
              $error = "Could not insert slot: " . $rodb->error;
              break 5;
            }
            $slot_id = $rodb->insert_id;

            foreach ($slot as $bt) {
              $insert_type->bind_param('ii', $slot_id, $bt);
              if (! $insert_type->execute()) {
                $error = "Could not insert boat type: " . $rodb->error;
                break 6;
              }
            }
          }
        }
      }
    }
  }
}

if (! is_null($error)) {
	$res['status'] = 'error';
	$res['error'] = $error;

	// Try to roll back.
	$log[] = "Rolling  back...";
	if ($rodb->rollback()) {
		$log[] = "Rollback may have succeeded";
	} else {
		$log[] = "Rollback failed :" . $rodb->error;
	}
} else {
	if ($rodb->commit()) {
		$log[] = "Committed changes to database";
	} else {
		$res['error'] = "Could not commit changes: " . $rodb->error;
		$res['status'] = 'error';
	}
}

$res['log'] = $log;
echo json_encode($res,JSON_PRETTY_PRINT);
$rodb->close();


?>
