<?php
include 'connectdb.php';

switch ($_POST["method"]) {
    case "getusers":
        getUsers();
        break;
    case "addmedecin":
        addMedecin();
        break;
    case "deletemedecin":
        deleteMedecin();
        break;
    case "editmedecin":
        editMedecin();
        break;
    case "addpharmacist":
        addPharmacist();
        break;
    case "deletepharmacist":
        deletePharmacist();
        break;
    case "editpharmacist":
        editPharmacist();
        break;
    case "editpatient":
        editPatient();
        break;
    case "deletepatient":
        deletePatient();
        break;
    case "changeemail":
        changeEmail();
        break;
    case "changepassword":
        changePassword();
        break;
    case "editprofile":
        editProfile();
        break;
    case "getmodifications":
        getModifications();
        break;
}

function getUsers()
{
    global $db;
    $medecins = $db->query("select * from utilisateur, medecin where utilisateur.id_utilisateur = medecin.id_utilisateur order by nom, prenom;");
    $pharmacists = $db->query("select * from utilisateur, pharmacien, pharmacie where utilisateur.id_utilisateur = pharmacien.id_utilisateur and pharmacien.id_pharmacie = pharmacie.id_pharmacie order by nom, prenom;");
    $patients = $db->query("select * from utilisateur, patient where utilisateur.id_utilisateur = patient.id_utilisateur order by nom, prenom;");
    $pharmacies = $db->query("select * from pharmacie;");
    $medecinslist = array();
    $pharmacistslist = array();
    $patientslist = array();
    $pharmacieslist = array();
    while ($row = mysqli_fetch_assoc($medecins)) {
        $medecinslist[] = $row;
    }
    while ($row = mysqli_fetch_assoc($pharmacists)) {
        $pharmacistslist[] = $row;
    }
    while ($row = mysqli_fetch_assoc($patients)) {
        $patientslist[] = $row;
    }
    while ($row = mysqli_fetch_assoc($pharmacies)) {
        $pharmacieslist[] = $row;
    }
    $response = array("medecins" => $medecinslist, "pharmacists" => $pharmacistslist, "patients" => $patientslist, "pharmacies" => $pharmacieslist);
    echo json_encode($response);
}

function addMedecin()
{
    global $db;
    $verify = $db->query("select * from utilisateur where email = '" . $_POST['email'] . "';");
    if (mysqli_num_rows($verify) == 1) {
        http_response_code(400);
    } else {
        $adduser = $db->query("insert into utilisateur (email, password, nom, prenom, phone, adresse, type) values ('" . $_POST["email"] . "','" . $_POST["password"] . "','" . $_POST["nom"] . "','" . $_POST["prenom"] . "','" . $_POST["phone"] . "','" . $_POST["adresse"] . "', 2);");
        $user = $db->query("select * from utilisateur where email = '" . $_POST["email"] . "';");
        $data = array();
        while ($row = mysqli_fetch_assoc($user)) {
            $data[] = $row;
        }
        $addmedecin = $db->query("insert into medecin (id_utilisateur, specialite) values (" . $data[0]["id_utilisateur"] . ",'" . $_POST["specialite"] . "');");
        echo ("success");
    }
}

function deleteMedecin()
{
    global $db;
    $sql = $db->query("DELETE FROM reclamation_fiche WHERE id_fiche IN (SELECT id_fiche FROM Fiche WHERE id_medecin =" . $_POST["id_medecin"] . ");");
    $sql = $db->query("DELETE FROM Dosage WHERE id_ordonnance IN (SELECT id_ordonnance FROM Ordonnance WHERE id_fiche IN (SELECT id_fiche FROM Fiche WHERE id_medecin = " . $_POST["id_medecin"] . "));");
    $sql = $db->query("DELETE FROM Livraison WHERE id_ordonnance IN (SELECT id_ordonnance FROM Ordonnance WHERE id_fiche IN (SELECT id_fiche FROM Fiche WHERE id_medecin = " . $_POST["id_medecin"] . "));");
    $sql = $db->query("DELETE FROM Ordonnance WHERE id_fiche IN (SELECT id_fiche FROM Fiche WHERE id_medecin = " . $_POST["id_medecin"] . ");");
    $sql = $db->query("DELETE FROM Fiche WHERE id_medecin = " . $_POST["id_medecin"] . ";");
    $sql = $db->query("DELETE FROM Token WHERE id_utilisateur = " . $_POST["id_utilisateur"] . ";");
    $sql = $db->query("DELETE FROM Reclamation_compte WHERE id_utilisateur = " . $_POST["id_utilisateur"] . ";");
    $sql = $db->query("delete from medecin where id_utilisateur =" . $_POST["id_utilisateur"] . ";");
    $sql = $db->query("DELETE FROM Utilisateur WHERE id_utilisateur = " . $_POST["id_utilisateur"] . ";");
    echo ("succes");
}

function editMedecin()
{
    global $db;
    $sql = $db->query("UPDATE Utilisateur SET nom='" . $_POST["nom"] . "', prenom='" . $_POST["prenom"] . "', phone='" . $_POST["phone"] . "', adresse='" . $_POST["adresse"] . "' WHERE id_utilisateur='" . $_POST["id_utilisateur"] . "';");
    $sql = $db->query("update medecin set specialite = '" . $_POST["specialite"] . "' where id_medecin = '" . $_POST["id_medecin"] . "';");
}

function addPharmacist()
{
    global $db;
    $verify = $db->query("select * from utilisateur where email = '" . $_POST['email'] . "';");
    if (mysqli_num_rows($verify) == 1) {
        http_response_code(400);
        echo ("failed");
    } else {
        $adduser = $db->query("insert into utilisateur (email, password, nom, prenom, phone, adresse, type) values ('" . $_POST["email"] . "','" . $_POST["password"] . "','" . $_POST["nom"] . "','" . $_POST["prenom"] . "','" . $_POST["phone"] . "','" . $_POST["adresse"] . "', 3);");
        $user = $db->query("select * from utilisateur where email = '" . $_POST["email"] . "';");
        $data = array();
        while ($row = mysqli_fetch_assoc($user)) {
            $data[] = $row;
        }
        $addmedecin = $db->query("insert into pharmacien (id_utilisateur, id_pharmacie) values (" . $data[0]["id_utilisateur"] . "," . $_POST["id_pharmacie"] . ");");
        echo ("success");
    }
}

function editPharmacist()
{
    global $db;
    $sql = $db->query("UPDATE Utilisateur SET nom='" . $_POST["nom"] . "', prenom='" . $_POST["prenom"] . "', phone='" . $_POST["phone"] . "', adresse='" . $_POST["adresse"] . "' WHERE id_utilisateur='" . $_POST["id_utilisateur"] . "';");
    $sql = $db->query("update pharmacien set id_pharmacie = '" . $_POST["id_pharmacie"] . "' where id_pharmacien = '" . $_POST["id_pharmacien"] . "';");
}

function deletePharmacist()
{
    global $db;
    $sql = $db->query("DELETE FROM Token WHERE id_utilisateur = " . $_POST["id_utilisateur"] . ";");
    $sql = $db->query("DELETE FROM Reclamation_compte WHERE id_utilisateur = " . $_POST["id_utilisateur"] . ";");
    $sql = $db->query("delete from pharmacien where id_utilisateur =" . $_POST["id_utilisateur"] . ";");
    $sql = $db->query("DELETE FROM Utilisateur WHERE id_utilisateur = " . $_POST["id_utilisateur"] . ";");
    echo ("succes");
}

function editPatient(){
    global $db;
    $sql = $db->query("UPDATE Utilisateur SET nom='" . $_POST["nom"] . "', prenom='" . $_POST["prenom"] . "', phone='" . $_POST["phone"] . "', adresse='" . $_POST["adresse"] . "' WHERE id_utilisateur='" . $_POST["id_utilisateur"] . "';");
    $sql = $db->query("update patient set date_n = '" . $_POST["date_n"] . "' where id_patient = '" . $_POST["id_patient"] . "';");
}

function deletePatient(){
    global $db;
    $sql = $db->query("DELETE FROM reclamation_fiche WHERE id_fiche IN (SELECT id_fiche FROM Fiche WHERE id_patient =" . $_POST["id_patient"] . ");");
    $sql = $db->query("DELETE FROM Dosage WHERE id_ordonnance IN (SELECT id_ordonnance FROM Ordonnance WHERE id_fiche IN (SELECT id_fiche FROM Fiche WHERE id_patient = " . $_POST["id_patient"] . "));");
    $sql = $db->query("DELETE FROM Livraison WHERE id_ordonnance IN (SELECT id_ordonnance FROM Ordonnance WHERE id_fiche IN (SELECT id_fiche FROM Fiche WHERE id_patient = " . $_POST["id_patient"] . "));");
    $sql = $db->query("DELETE FROM Ordonnance WHERE id_fiche IN (SELECT id_fiche FROM Fiche WHERE id_patient = " . $_POST["id_patient"] . ");");
    $sql = $db->query("DELETE FROM Fiche WHERE id_patient = " . $_POST["id_patient"] . ";");
    $sql = $db->query("DELETE FROM Token WHERE id_utilisateur = " . $_POST["id_utilisateur"] . ";");
    $sql = $db->query("DELETE FROM Reclamation_compte WHERE id_utilisateur = " . $_POST["id_utilisateur"] . ";");
    $sql = $db->query("delete from patient where id_utilisateur =" . $_POST["id_utilisateur"] . ";");
    $sql = $db->query("DELETE FROM Utilisateur WHERE id_utilisateur = " . $_POST["id_utilisateur"] . ";");
    echo ("succes");
}

function editProfile(){
    global $db;
    $sql = $db->query("update utilisateur set prenom ='".$_POST["prenom"]."', nom ='".$_POST["nom"]."', phone='".$_POST["phone"]."', adresse ='".$_POST["adresse"]."' where id_utilisateur =".$_POST["id_utilisateur"].";");
    $info = $db->query("select * from utilisateur where id_utilisateur =" . $_POST['id_utilisateur'] . ";");
    $user = array();
    while ($row = mysqli_fetch_assoc($info)) {
        $user[] = $row;
    }
    echo json_encode($user);
}

function changeEmail(){
    global $db;
    $verify = $db->query("select * from utilisateur where email ='".$_POST["email"]."';");
    if(mysqli_num_rows($verify) == 1){
        http_response_code(400);
    }else{
        $sql = $db->query("update utilisateur set email ='".$_POST["email"]."' where id_utilisateur = ".$_POST["id_utilisateur"].";");
        $info = $db->query("select * from utilisateur where id_utilisateur =" . $_POST['id_utilisateur'] . ";");
        $user = array();
        while ($row = mysqli_fetch_assoc($info)) {
            $user[] = $row;
        }
        echo json_encode($user);
    }
}

function changePassword(){
    global $db;
    $sql = $db->query("update utilisateur set password ='".$_POST["password"]."' where id_utilisateur = ".$_POST["id_utilisateur"].";");
    $info = $db->query("select * from utilisateur where id_utilisateur =" . $_POST['id_utilisateur'] . ";");
    $user = array();
    while ($row = mysqli_fetch_assoc($info)) {
        $user[] = $row;
    }
    echo json_encode($user);
}

function getModifications(){
    global $db;
    $sql = $db->query("select * from reclamation_compte, utilisateur where reclamation_compte.id_utilisateur = utilisateur.id_utilisateur order by id_reclamation desc;");
    $modifications = array();
    while ($row = mysqli_fetch_assoc($sql)) {
        $modifications[] = $row;
    }
    echo json_encode($modifications);
}