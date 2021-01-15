<?php


namespace App\Controllers;


class PkmPveController
{
    public function storePkmPve()
    {
        $newPkm = [
            "name" => $_POST['pkmpve'],
            "role" => $_POST['role'],
            "cp" => $_POST['cp'],
            "lv" => $_POST['lv'],
            "sta_iv" => $_POST['sta_iv'],
            "def_iv" => $_POST['def_iv'],
            "atk_iv" => $_POST['atk_iv'],
            "iv_percentage" => $_POST['percentage_iv'],
            "quick_move" => $_POST['quick_move-pkmpve'],
            "charge1_move" => $_POST['charge1_move-pkmpve'],
            "charge2_move" => $_POST['charge2_move-pkmpve'],
        ];

        $pkmsPvp = file_get_contents('includes/files/pkm_pve.json');
        $pkmsPvp = json_decode($pkmsPvp, true);

        $pkmsPvp[$_POST['idpkmpve']] = $newPkm;
        file_put_contents('includes/files/pkm_pve.json', json_encode($pkmsPvp, JSON_PRETTY_PRINT));

        $newRow = "<tr id='$_POST[idpkmpve]' class='pkm-pve-row' ><td>$_POST[pkmpve]</td><td>$_POST[cp]</td><td>$_POST[lv]</td><td>$_POST[sta_iv]</td><td>$_POST[def_iv]</td><td>$_POST[atk_iv]</td><td>$_POST[percentage_iv]</td><td></td><td></td><td></td><td>$_POST[role]</td></tr>";

        $nextId = max(array_keys($pkmsPvp)) + 1;

        $result = [
            'newRow' => $newRow,
            'nextId' => $nextId,
        ];

        echo json_encode($result);
    }

    public function deletePkmPve($idPkm)
    {
        $pkmsPvp = file_get_contents('includes/files/pkm_pve.json');
        $pkmsPvp = json_decode($pkmsPvp, true);

        unset($pkmsPvp[$idPkm]);
        file_put_contents('includes/files/pkm_pve.json', json_encode($pkmsPvp, JSON_PRETTY_PRINT));
    }

    public function getPkmPve($idPkm)
    {
        $pkmsPvp = file_get_contents('includes/files/pkm_pve.json');
        $pkmsPvp = json_decode($pkmsPvp, true);
        echo json_encode($pkmsPvp[$idPkm]);
    }

    public function updatePkmPve($idPkm)
    {
        $pkmsPvp = file_get_contents('includes/files/pkm_pve.json');
        $pkmsPvp = json_decode($pkmsPvp, true);
        $pkmPvp = $pkmsPvp[$idPkm];

        $pkmPvp = [
            "name" => $pkmPvp['name'],
            "role" => $_POST['role_edt'],
            "cp" => $_POST['cp_edt'],
            "lv" => $_POST['lv_edt'],
            "sta_iv" => $_POST['sta_iv_edt'],
            "def_iv" => $_POST['def_iv_edt'],
            "atk_iv" => $_POST['atk_iv_edt'],
            "iv_percentage" => $_POST['percentage_iv_edt'],
        ];

        $pkmsPvp[$idPkm] = $pkmPvp;
        file_put_contents('includes/files/pkm_pve.json', json_encode($pkmsPvp, JSON_PRETTY_PRINT));

        $updatedRow = "<tr id='$idPkm' class='pkm-pve-row' ><td>$pkmPvp[name]</td><td>$_POST[cp_edt]</td><td>$_POST[lv_edt]</td><td>$_POST[sta_iv_edt]</td><td>$_POST[def_iv_edt]</td><td>$_POST[atk_iv_edt]</td><td>$_POST[percentage_iv_edt]</td><td></td><td></td><td></td><td>$_POST[role_edt]</td></tr>";
        echo $updatedRow;
    }
}