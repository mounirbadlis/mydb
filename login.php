<?php
include 'connectdb.php';

switch ($_POST['method']) {
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'getuser':
        getUsers();
        break;
    case 'trytoken':
        tryToken();
        break;
    case "editprofile":
        editProfile();
        break;
}

function login()
{
    global $db;
    $login = $db->query("select * from utilisateur where email ='" . $_POST['email'] . "'and password ='" . $_POST['password'] . "';");
    $data = array();
    while ($row = mysqli_fetch_assoc($login)) {
        $data[] = $row;
    }
    if (mysqli_num_rows($login) == 1) {
        $token = uniqid();
        $db->query("insert into token (id_utilisateur, valeur, device) values (" . $data[0]['id_utilisateur'] . ",'" . $token . "','" . $_POST['device'] . "');");
        switch ($data[0]['type']) {
            case 1:
                $info = $db->query("select * from utilisateur where email ='" . $_POST['email'] . "'and password ='" . $_POST['password'] . "';");
                break;
            case 2:
                $info = $db->query("select * from utilisateur, medecin where utilisateur.id_utilisateur = medecin.id_utilisateur
                 and utilisateur.id_utilisateur = '" . $data[0]["id_utilisateur"] . "';");
                break;
            case 3:
                $info = $db->query("select * from utilisateur, pharmacien, pharmacie where utilisateur.id_utilisateur = pharmacien.id_utilisateur
                 and pharmacien.id_pharmacie = pharmacie.id_pharmacie and utilisateur.id_utilisateur = '" . $data[0]["id_utilisateur"] . "';");
                break;
            case 4:
                $info = $db->query("select * from utilisateur, patient where utilisateur.id_utilisateur = patient.id_utilisateur
                 and utilisateur.id_utilisateur = '" . $data[0]["id_utilisateur"] . "';");
                break;
        }
        $user = array();
        while ($row = mysqli_fetch_assoc($info)) {
            $user[] = $row;
        }
        $response = array('user' => $user, 'token' => $token);
        echo json_encode($response);
    } else {
        http_response_code(400);
        $response = array('status' => 'error');
        echo json_encode($response);
    }
}

function logout()
{
    global $db;
    $logout = $db->query("delete from token where valeur ='" . $_POST['token'] . "';");
    if ($logout) {
        echo "done!";
    }
}

function getUsers()
{
    global $db;
    $users = $db->query('select * from utilisateur');
    $data = array();
    while ($row = mysqli_fetch_assoc($users)) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function tryToken()
{
    global $db;
    $res = $db->query("select * from token where valeur = '" . $_POST['valeur'] . "';");
    if (mysqli_num_rows($res) == 1) {
        $user = $db->query("select * from utilisateur, token where utilisateur.id_utilisateur = token.id_utilisateur and token.valeur = '" . $_POST['valeur'] . "';");
        $data = array();
        while ($row = mysqli_fetch_assoc($user)) {
            $data[] = $row;
        }
        switch ($data[0]['type']) {
            case 1:
                $info = $db->query("select * from utilisateur where id_utilisateur ='" . $data[0]["id_utilisateur"] . "';");
                break;
            case 2:
                $info = $db->query("select * from utilisateur, medecin where utilisateur.id_utilisateur = medecin.id_utilisateur and utilisateur.id_utilisateur = '" . $data[0]["id_utilisateur"]. "';");
                break;
            case 3:
                $info = $db->query("select * from utilisateur, pharmacien, pharmacie where utilisateur.id_utilisateur = pharmacien.id_utilisateur and pharmacien.id_pharmacie = pharmacie.id_pharmacie and utilisateur.id_utilisateur = '" . $data[0]["id_utilisateur"] . "';");
                break;
            case 4:
                $info = $db->query("select * from utilisateur, patient where utilisateur.id_utilisateur = patient.id_utilisateur and utilisateur.id_utilisateur = '" . $data[0]["id_utilisateur"] . "';");
                break;
        }
        $user = array();
        while ($row = mysqli_fetch_assoc($info)) {
            $user[] = $row;
        }
        $response = array('user' => $user);
        echo json_encode($response);
    } else {
        http_response_code(400);
    }
}

function editProfile(){
    global $db;
    $sql = $db->query("insert into reclamation_compte(id_utilisateur, changes, etat) values(".$_POST["id_utilisateur"].",'".$_POST["changes"]."', 0);");
}
