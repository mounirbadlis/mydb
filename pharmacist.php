<?php
include 'connectdb.php';

switch($_POST["method"]){
    case "getmedicines":
        getMedicines();
        break;
    case "getstock":
        getStock();
        break;
    case "deletestock":
        deleteStock();
        break;
    case "editstock":
        editStock();
        break;
    case "addstock":
        addStock();
        break;
    case "orders":
        orders();
        break;
    case "selectedorder":
        selectedOrder();
        break;
    case "acceptorder":
        acceptOrder();
        break;
    case "refuseorder":
        refuseOrder();
        break;
    case "updateorder":
        updateOrder();
        break;
    case "addmedicine":
        addMedicine();
        break;
    case "changepassword":
        changePassword();
        break;
    case "changeemail":
        changeEmail();
        break;
}

function getMedicines(){
    global $db;
    $medicines = $db->query("select * from medicament order by nom_m;");
    $data = array();
    while($row = mysqli_fetch_assoc($medicines)){
        $data[] = $row;
    }
    echo json_encode($data);
}

function getStock(){
    global $db;
    $sql = $db->query("select * from medicament, stock where medicament.id_medicament = stock.id_medicament and stock.id_pharmacie =".$_POST["id_pharmacie"]." order by nom_m;");
    $data = array();
    while($row = mysqli_fetch_assoc($sql)){
        $data[] = $row;
    }
    echo json_encode($data);
}

function deleteStock(){
    global $db;
    $sql = $db->query("delete from stock where id_stock =".$_POST["id_stock"].";");
}

function editStock(){
    global $db;
    $sql = $db->query("update stock set quantite =".$_POST["quantite"]." where id_stock = ".$_POST["id_stock"].";");

}

function addStock(){
    global $db;
    $verify = $db->query("select * from stock where id_medicament =".$_POST["id_medicament"]." and id_pharmacie = ".$_POST["id_pharmacie"].";");
    if(mysqli_num_rows($verify) == 1){
        http_response_code(400);
    }else{
        $sql = $db->query("insert into stock (id_medicament, id_pharmacie, quantite) values (".$_POST["id_medicament"].", ".$_POST["id_pharmacie"].", ".$_POST["quantite"].");");
    }
}

function orders(){
    global $db;
    $orders_p = $db->query("select id_livraison, livraison.id_ordonnance, date, id_pharmacie, etat, nom, prenom, adresse, phone from livraison, ordonnance, fiche, patient, utilisateur where id_pharmacie =".$_POST["id_pharmacie"]." and livraison.id_ordonnance = ordonnance.id_ordonnance and ordonnance.id_fiche = fiche.id_fiche and fiche.id_patient = patient.id_patient and patient.id_utilisateur = utilisateur.id_utilisateur and livraison.etat not in(3) order by id_livraison desc");
    $orders_m = $db->query("select id_livraison, livraison.id_ordonnance, date, id_pharmacie, etat, nom, prenom, adresse, phone from livraison, ordonnance, fiche, medecin, utilisateur where id_pharmacie =".$_POST["id_pharmacie"]." and livraison.id_ordonnance = ordonnance.id_ordonnance and ordonnance.id_fiche = fiche.id_fiche and fiche.id_medecin = medecin.id_medecin and medecin.id_utilisateur = utilisateur.id_utilisateur and livraison.etat not in(3) order by id_livraison desc");
    $data_p = array();
    $data_m = array();
    while($row = mysqli_fetch_assoc($orders_p)){
        $data_p[] = $row;
    }
    while($row = mysqli_fetch_assoc($orders_m)){
        $data_m[] = $row;
    }
    $response = array("orders_patients" => $data_p, "orders_doctors" => $data_m);
    echo json_encode($response);
}

function selectedOrder(){
    global $db;
    $medicines = $db->query("select * from dosage, medicament where dosage.id_medicament = medicament.id_medicament and id_ordonnance =".$_POST["id_ordonnance"]." order by nom_m;");
    $data = array();
    while($row = mysqli_fetch_assoc($medicines)){
        $data[] = $row;
    }
    echo json_encode($data);
}

function acceptOrder(){
    global $db;
    $sql = $db->query("update livraison set etat = 1 where id_livraison ='".$_POST["id_livraison"]."';");
}

function refuseOrder(){
    global $db;
    $sql = $db->query("update livraison set etat = 3 where id_livraison ='".$_POST["id_livraison"]."';");
}

function updateOrder(){
    global $db;
    if($_POST["etat"] == "1"){
        $sql = $db->query("update livraison set etat ='".$_POST["etat"]."' where id_livraison =".$_POST["id_livraison"].";");
    }else{
        $medicines = $db->query("select * from dosage, medicament where dosage.id_medicament = medicament.id_medicament 
        and id_ordonnance =".$_POST["id_ordonnance"]." order by nom_m;");
        $data = array();
        while($row = mysqli_fetch_assoc($medicines)){
            $data[] = $row;
        }
        foreach($data as $medicine){
            $mines = $db->query("update stock set quantite = quantite -".$medicine["nbr_b"]." 
            where id_medicament =".$medicine["id_medicament"]." and id_pharmacie = ".$_POST["id_pharmacie"].";");
        }
        $sql = $db->query("update livraison set etat ='".$_POST["etat"]."' where id_livraison =".$_POST["id_livraison"].";");
    }
}

function addMedicine(){
    global $db;
    $verify = $db->query("select * from medicament where upper(nom_m) = upper('".$_POST["nom_m"]."');");
    if(mysqli_num_rows($verify) == 1){
        http_response_code(400);
    }else{
        $add = $db->query("insert into medicament(nom_m, prix) values ( upper('".$_POST["nom_m"]."'), ".$_POST["prix"].");");
    }
}

function changeEmail(){
    global $db;
    $verify = $db->query("select * from utilisateur where email ='".$_POST["email"]."';");
    if(mysqli_num_rows($verify) == 1){
        http_response_code(400);
    }else{
        $sql = $db->query("update utilisateur set email ='".$_POST["email"]."' where id_utilisateur = ".$_POST["id_utilisateur"].";");
        $info = $db->query("select * from utilisateur, pharmacien, pharmacie where utilisateur.id_utilisateur = pharmacien.id_utilisateur and pharmacien.id_pharmacie = pharmacie.id_pharmacie and pharmacien.id_utilisateur =" . $_POST['id_utilisateur'] . ";");
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
    $info = $db->query("select * from utilisateur, pharmacien, pharmacie where utilisateur.id_utilisateur = pharmacien.id_utilisateur and pharmacien.id_pharmacie = pharmacie.id_pharmacie and pharmacien.id_utilisateur =" . $_POST['id_utilisateur'] . ";");
    $user = array();
    while ($row = mysqli_fetch_assoc($info)) {
        $user[] = $row;
    }
    echo json_encode($user);
}
?>