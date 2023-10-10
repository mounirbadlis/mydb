<?php 
include "connectdb.php";

switch($_POST["method"]){
    case "myorders":
        myOrders();
        break;
    case "myrecords":
        myRecords();
        break;
    case "selectedrecord":
        selectedRecord();
        break;
    case "selectedprescription":
        SelectedPrescription();
        break;
    case "renewalrequest":
        renewalRequest();
        break;
    case "sendcomplaint":
        sendComplaint();
        break;
    case "mycomplaints":
        myComplaints();
        break;
    case "changeemail":
        changeEmail();
        break;
    case "changepassword":
        changePassword();
        break;
}

function myOrders(){
    global $db;
    $sql = $db->query("select * from livraison, ordonnance, fiche, patient, utilisateur, pharmacie where livraison.id_ordonnance = ordonnance.id_ordonnance and ordonnance.id_fiche = fiche.id_fiche and fiche.id_patient = patient.id_patient and patient.id_utilisateur = utilisateur.id_utilisateur and livraison.id_pharmacie = pharmacie.id_pharmacie and patient.id_patient =".$_POST["id_patient"]." order by id_livraison desc;");
    $sql2 = $db->query("select * from livraison, ordonnance, fiche, medecin, utilisateur, pharmacie where livraison.id_ordonnance = ordonnance.id_ordonnance and ordonnance.id_fiche = fiche.id_fiche and fiche.id_medecin = medecin.id_medecin and medecin.id_utilisateur = utilisateur.id_utilisateur and livraison.id_pharmacie = pharmacie.id_pharmacie and fiche.id_patient =".$_POST["id_patient"]." order by id_livraison desc;");
    $data = array();
    $data2 = array();
    while($row = mysqli_fetch_assoc($sql)){
        $data[] = $row;
    }
    while($row = mysqli_fetch_assoc($sql2)){
        $data2[] = $row;
    }
    $response = array("orders_patient" => $data, "orders_doctor" => $data2);
    echo json_encode($response);
}

function myRecords(){
    global $db;
    $sql = $db->query("select * from fiche, patient, utilisateur where fiche.id_patient = patient.id_patient and patient.id_utilisateur = utilisateur.id_utilisateur and patient.id_patient =".$_POST["id_patient"]." order by type_f;");
    $sql2 = $db->query("select * from fiche, medecin, utilisateur where fiche.id_medecin = medecin.id_medecin and medecin.id_utilisateur = utilisateur.id_utilisateur and fiche.id_patient =".$_POST["id_patient"]." order by type_f;");
    $data = array();
    $data2 = array();
    while($row = mysqli_fetch_assoc($sql)){
        $data[] = $row;
    }
    while($row = mysqli_fetch_assoc($sql2)){
        $data2[] = $row;
    }
    $response = array("records_patient" => $data, "records_doctor" => $data2);
    echo json_encode($response);
}

function selectedRecord(){
    global $db;
    global $db;
    $prescriptions = $db->query("select * from ordonnance, livraison where livraison.id_ordonnance = ordonnance.id_ordonnance and etat = '2' and id_fiche = ".$_POST["id_fiche"]." order by date desc;");
    $data = array();
    while($row = mysqli_fetch_assoc($prescriptions)){
        $data[] = $row;
    }
    echo json_encode($data);
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

function renewalRequest(){
    global $db;
    $sql = $db->query("insert into reclamation_fiche (id_fiche, info, date_r, etat) values (".$_POST["id_fiche"].", 'prescription renewal request of ".$_POST["date"]."','".$_POST["date_r"]."', '0');");
}

function sendComplaint(){
    global $db;
    $sql = $db->query("insert into reclamation_fiche (id_fiche, info, date_r, etat) values (".$_POST["id_fiche"].", 'prescription of ".$_POST["date"].":".mysqli_real_escape_string($db, $_POST['info'])."','".$_POST["date_r"]."', '0');");
}

function myComplaints(){
    global $db;
    $complains = $db->query("select * from reclamation_fiche, fiche, medecin, patient, utilisateur where reclamation_fiche.id_fiche = fiche.id_fiche and fiche.id_medecin = medecin.id_medecin and fiche.id_patient = patient.id_patient and medecin.id_utilisateur = utilisateur.id_utilisateur and fiche.id_patient =".$_POST["id_patient"]." order by date_r desc");
    $data = array();
    while($row = mysqli_fetch_assoc($complains)){
        $data[] = $row;
    }
    echo json_encode($data);
}

function changeEmail(){
    global $db;
    $verify = $db->query("select * from utilisateur where email ='".$_POST["email"]."';");
    if(mysqli_num_rows($verify) == 1){
        http_response_code(400);
    }else{
        $sql = $db->query("update utilisateur set email ='".$_POST["email"]."' where id_utilisateur = ".$_POST["id_utilisateur"].";");
        $info = $db->query("select * from utilisateur, patient where utilisateur.id_utilisateur = patient.id_utilisateur and patient.id_utilisateur =" . $_POST['id_utilisateur'] . ";");
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
    $info = $db->query("select * from utilisateur, patient where utilisateur.id_utilisateur = patient.id_utilisateur and patient.id_utilisateur =" . $_POST['id_utilisateur'] . ";");
    $user = array();
    while ($row = mysqli_fetch_assoc($info)) {
        $user[] = $row;
    }
    echo json_encode($user);
}
?>