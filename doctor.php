<?php 
include 'connectdb.php';

switch($_POST["method"]){
    case "getpatients":
        getPatients();
        break;
    case "addpatient":
        addPatient();
        break;
    case "createrecord":
        createRecord();
        break;
    case "mypatients":
        myPatients();
        break;
    case "selectedpatient":
        selectedPatient();
        break;
    case "editrecord":
        editRecord();
        break;
    case "deleterecord":
        deleteRecord();
        break;
    case "getprescriptions":
        getPrescriptions();
        break;
    case "createprescription":
        createPrescription();
        break;
    case "selectedprescription":
        selectedPrescription();
        break;
    case "getpharmacies":
        getPharmacies();
        break;
    case "getstock":
        getStock();
        break;
    case "getmedicinespharmacies":
        getMedicinesPharmacies();
        break;
    case "verifydelivery":
        verifyDelivery();
        break;
    case "sendprescription":
        sendPrescription();
        break;
    case "addmedicine":
        addMedicine();
        break;
    case "verifypharmacy":
        verifyPharmacy();
        break;
    case "modifydosage":
        modifyDosage();
        break;
    case "removemedicine":
        removeMedicine();
        break;
    case "renewprescription":
        renewPrescription();
        break;
    case "getcomplaints":
        getComplaints();
        break;
    case "sendresponse":
        sendResponse();
        break;
    case "changeemail":
        changeEmail();
        break;
    case "changepassword":
        changePassword();
        break;
}

function getPatients(){
    global $db;
    $patients = $db->query("select * from utilisateur, patient where utilisateur.id_utilisateur = patient.id_utilisateur and id_patient  not in (select fiche.id_patient from fiche, medecin,utilisateur where fiche.id_medecin = medecin.id_medecin and medecin.id_utilisateur = utilisateur.id_utilisateur and utilisateur.id_utilisateur =".$_POST["id_utilisateur"].") order by nom, prenom;");
    $medecinslist = array();
    $patientslist = array();
    while ($row = mysqli_fetch_assoc($patients)) {
        $patientslist[] = $row;
    }
    while ($row = mysqli_fetch_assoc($patients)) {
        $patientslist[] = $row;
    }
    echo json_encode($patientslist);
}

function addPatient(){
    global $db;
    $verify = $db->query("select * from utilisateur where email = '".$_POST["email"]."';");
    if(mysqli_num_rows($verify) == 1){
        http_response_code(400);
        echo "faild";
    }else{
        $adduser = $db->query("insert into utilisateur (email, password, nom, prenom, phone, adresse, type) values ('" . $_POST["email"] . "','" . $_POST["password"] . "','" . $_POST["nom"] . "','" . $_POST["prenom"] . "','" . $_POST["phone"] . "','" . $_POST["adresse"] . "', 4);");
        $user = $db->query("select * from utilisateur where email = '" . $_POST["email"] . "';");
        $data = array();
        while ($row = mysqli_fetch_assoc($user)) {
            $data[] = $row;
        }
        $addpatient = $db->query("insert into patient (id_utilisateur, date_n) values (".$data[0]["id_utilisateur"].", '".$_POST["date_n"]."');");
    }
}

function createRecord(){
    global $db;
    $verify = $db->query("select * from fiche where id_patient = ".$_POST["id_patient"]." and id_medecin = ".$_POST["id_medecin"]." and upper(type_f) = upper('".$_POST["type_f"]."');");
    if(mysqli_num_rows($verify) == 1){
        http_response_code(400);
        echo "faild";
    }else{
        $create = $db->query("insert into fiche (id_medecin, id_patient, type_f) values (".$_POST["id_medecin"].", ".$_POST["id_patient"].", '".$_POST["type_f"]."');");
        echo "success";
    }
}

function myPatients(){
    global $db;
    $info = $db->query("select * from patient, utilisateur where patient.id_utilisateur = utilisateur.id_utilisateur and patient.id_patient in(select patient.id_patient from medecin, patient, fiche, utilisateur where fiche.id_medecin = medecin.id_medecin and fiche.id_patient = patient.id_patient and patient.id_utilisateur = utilisateur.id_utilisateur and medecin.id_medecin = ".$_POST["id_medecin"]." order by nom, prenom);");
    $patients = array();
    while($row = mysqli_fetch_assoc($info)){
        $patients[] = $row;
    }
    echo json_encode($patients);
}

function selectedPatient(){
    global $db;
    $info = $db->query("select * from medecin, patient, fiche, utilisateur where fiche.id_medecin = medecin.id_medecin and fiche.id_patient = patient.id_patient and patient.id_utilisateur = utilisateur.id_utilisateur and patient.id_patient = ".$_POST["id_patient"]." and medecin.id_medecin = ".$_POST["id_medecin"]." order by nom, prenom;");
    $sheets = array();
    while($row = mysqli_fetch_assoc($info)){
        $sheets[] = $row;
    }
    echo json_encode($sheets);
}

function editRecord(){
    global $db;
    $sql = $db->query("update fiche set type_f = '".$_POST["type_f"]."' where id_fiche = ".$_POST["id_fiche"].";");
}

function deleteRecord(){
    global $db;
    $sql = $db->query("DELETE FROM Dosage WHERE id_ordonnance IN (SELECT id_ordonnance FROM Ordonnance WHERE id_fiche = " .$_POST["id_fiche"].");");
    $sql = $db->query("DELETE FROM Livraison WHERE id_ordonnance IN (SELECT id_ordonnance FROM Ordonnance WHERE id_fiche = " .$_POST["id_fiche"].");");
    $sql = $db->query("DELETE FROM Ordonnance WHERE id_fiche = " .$_POST["id_fiche"].";");
    $sql = $db->query("DELETE FROM Reclamation_fiche WHERE id_fiche = " .$_POST["id_fiche"].";");
    $sql = $db->query("DELETE FROM Fiche WHERE id_fiche = " .$_POST["id_fiche"].";");

}

function getPrescriptions(){
    global $db;
    $prescriptions = $db->query("select * from ordonnance where id_fiche = ".$_POST["id_fiche"]." order by date desc;");
    $data = array();
    while($row = mysqli_fetch_assoc($prescriptions)){
        $data[] = $row;
    }
    echo json_encode($data);
}

function createPrescription(){
    global $db;
    $sql = $db->query("insert into ordonnance (id_fiche, date) values (".$_POST["id_fiche"].", '".$_POST["date"]."');");
}

function selectedPrescription(){
    global $db;
    $medicines = $db->query("select * from medicament, dosage where medicament.id_medicament = dosage.id_medicament and dosage.id_ordonnance = ".$_POST["id_ordonnance"]." order by medicament.nom_m;");
    $data = array();
    while($row = mysqli_fetch_assoc($medicines)){
        $data[] = $row;
    }
    echo json_encode($data);

}

function getPharmacies(){
    global $db;
    $pharmacies = $db->query("SELECT p.id_pharmacie, p.nom_ph, p.adresse_ph, COUNT(s.id_pharmacie) FROM pharmacie p LEFT JOIN stock s ON p.id_pharmacie = s.id_pharmacie GROUP BY p.id_pharmacie order by p.nom_ph;");
    $data = array();
    while($row = mysqli_fetch_assoc($pharmacies)){
        $data[] = $row;
    }
    echo json_encode($data);
}

function getStock(){
    global $db;
    $stock = $db->query("select * from medicament, stock where medicament.id_medicament = stock.id_medicament and stock.id_pharmacie =".$_POST["id_pharmacie"]." order by medicament.nom_m;");
    $data = array();
    while($row = mysqli_fetch_assoc($stock)){
        $data[] = $row;
    }
    echo json_encode($data);
}

function getMedicinesPharmacies(){
    global $db;
    $pharmacies = $db->query("select * from pharmacie order by nom_ph;");
    $medicines = $db->query("select * from medicament order by nom_m;");
    $data_m = array();
    $data_ph = array();
    while($row = mysqli_fetch_assoc($medicines)){
        $data_m[] = $row;
    }
    while($row = mysqli_fetch_assoc($pharmacies)){
        $data_ph[] = $row;
    }
    $response = array("medicines" => $data_m, "pharmacies" => $data_ph);
    echo json_encode($response);
}

function verifyDelivery(){
    global $db;
    $verify = $db->query("select * from ordonnance, livraison, pharmacie where ordonnance.id_ordonnance = livraison.id_ordonnance and livraison.id_pharmacie = pharmacie.id_pharmacie and livraison.id_ordonnance =".$_POST["id_ordonnance"].";");
    if(mysqli_num_rows($verify) == 0){
        http_response_code(400);
    }else{
        $data = array();
        while($row = mysqli_fetch_assoc($verify)){
            $data[] = $row;
        }
        if($data[0]["etat"] == 3){
            http_response_code(400);
        }else{
            http_response_code(200);
            echo json_encode($data);
        }
    }
}

function sendPrescription(){
    global $db;
    $sql = $db->query("insert into livraison (id_ordonnance, id_pharmacie, etat) values (".$_POST["id_ordonnance"].", ".$_POST["id_pharmacie"].", 0);");
}

function addMedicine(){
    global $db;
    $verify = $db->query("select * from dosage where id_medicament = ".$_POST["id_medicament"]." and id_ordonnance = ".$_POST["id_ordonnance"].";");
    if(mysqli_num_rows($verify) == 1){
        http_response_code(400);
    }else{
        $sql = $db->query("insert into dosage (id_ordonnance, id_medicament, dosage, nbr_b) values (".$_POST["id_ordonnance"].", ".$_POST["id_medicament"].", ".$_POST["dosage"].", ".$_POST["nbr_b"].");");
    }
}

function verifyPharmacy(){
    global $db;
    $getmedicines = $db->query("select * from dosage where id_ordonnance =".$_POST["id_ordonnance"].";");
    $medicines = array();
    while($row = mysqli_fetch_assoc($getmedicines)){
        $medicines[] = $row;
    }
    $getpharmacies = $db->query("select * from pharmacie;");
    $pharamcies = array();
    while($row = mysqli_fetch_assoc($getpharmacies)){
        $pharamcies[] = $row;
    }
    $result = array();
    foreach($pharamcies as $pharmacy){
        $verify = true;
        foreach($medicines as $medicine){
            $sql = $db->query("select * from stock where id_pharmacie = ".$pharmacy["id_pharmacie"]." and id_medicament =".$medicine["id_medicament"]." and quantite >= ".$medicine["nbr_b"].";");
            if(mysqli_num_rows($sql) == 0){
                $verify = false;
                break;
            }
        }
        if($verify){
            $result[] = $pharmacy;
        }
    }
    echo json_encode($result);
}

function modifyDosage(){
    global $db;
    $sql = $db->query("update dosage set dosage =".$_POST["dosage"]." where id_ordonnance =".$_POST["id_ordonnance"]." and id_medicament = ".$_POST["id_medicament"].";");
}

function removeMedicine(){
    global $db;
    $sql = $db->query("delete from dosage where id_medicament =".$_POST["id_medicament"].";");
}

function renewPrescription(){
    global $db;
    $get = $db->query("select * from dosage where id_ordonnance =".$_POST["id_ordonnance"].";");
    $medicines = array();
    while($row = mysqli_fetch_assoc($get)){
        $medicines[] = $row;
    }
    $insert = $db->query("insert into ordonnance (id_fiche, date) values (".$_POST["id_fiche"].", '".$_POST["date"]."');");
    $last = $db->query("select * from ordonnance where id_ordonnance in (select max(id_ordonnance) from ordonnance);");
    $res = array();
    while($row = mysqli_fetch_assoc($last)){
        $res[] = $row;
    }
    $id_ordonnance = $res[0]["id_ordonnance"];
    foreach($medicines as $medicine){
        $sql = $db->query("insert into dosage (id_ordonnance, id_medicament, nbr_b, dosage) 
        values (".$id_ordonnance.", ".$medicine["id_medicament"].", ".$medicine["nbr_b"].", ".$medicine["dosage"].");");
    }
}

function getComplaints(){
    global $db;
    $complains = $db->query("select * from reclamation_fiche, fiche, medecin, patient, utilisateur where reclamation_fiche.id_fiche = fiche.id_fiche and fiche.id_medecin = medecin.id_medecin and fiche.id_patient = patient.id_patient and patient.id_utilisateur = utilisateur.id_utilisateur and fiche.id_medecin =".$_POST["id_medecin"]." and etat = '0' order by date_r, id_reclamation_fiche desc");
    $data = array();
    while($row = mysqli_fetch_assoc($complains)){
        $data[] = $row;
    }
    echo json_encode($data);
}

function sendResponse(){
    global $db;
    $sql = $db->query("update reclamation_fiche set reponse = '".mysqli_real_escape_string($db, $_POST['reponse'])."', etat = '1' where id_reclamation_fiche =".$_POST["id_reclamation_fiche"].";");
}

function changeEmail(){
    global $db;
    $verify = $db->query("select * from utilisateur where email ='".$_POST["email"]."';");
    if(mysqli_num_rows($verify) == 1){
        http_response_code(400);
    }else{
        $sql = $db->query("update utilisateur set email ='".$_POST["email"]."' where id_utilisateur = ".$_POST["id_utilisateur"].";");
        $info = $db->query("select * from utilisateur, medecin where utilisateur.id_utilisateur = medecin.id_utilisateur and medecin.id_utilisateur =" . $_POST['id_utilisateur'] . ";");
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
    $info = $db->query("select * from utilisateur, medecin where utilisateur.id_utilisateur = medecin.id_utilisateur and medecin.id_utilisateur =" . $_POST['id_utilisateur'] . ";");
    $user = array();
    while ($row = mysqli_fetch_assoc($info)) {
        $user[] = $row;
    }
    echo json_encode($user);
}
?>