<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions 
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies 
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD {
	    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    protected function traitementSelect(string $table, ?array $champs) : ?array{
        switch($table){  
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplairesRevue($champs);
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            case "suivi" :
                return $this->selectAllSuivi($champs);
            case "abonnement" :
                return $this->selectAllAbonnement($champs);
            case "finAbonnement":
                return $this->selectAllFinAbonnement();
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }	
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */	
    protected function traitementInsert(string $table, ?array $champs) : ?int{
        switch($table){
            case "suivi" :
                return $this->insertNewSuivi($champs);
            case "abonnement" :
                return $this->insertNewAbonnement($champs);
            default:                    
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);	
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int{
        switch($table){
            case "suivi" :
                return $this->updateSuivi($champs);
            case "abonnement" :
                return $this->updateAbonnement($champs);
            default:                    
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }	
    }  
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    protected function traitementDelete(string $table, ?array $champs) : ?int{
        switch($table){
            case "suivi" :
                return $this->deleteSuivi($champs);
            case "abonnement" :
                return $this->deleteAbonnement($champs);
            default:                    
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }	    
        
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	          
            return $this->conn->queryBDD($requete, $champs);
        }
    }	

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }
    
    private function insertNewSuivi(?array $champs) : ?int{
        
        if(empty($champs)){
            return null;
        }

        $id = $champs["IdCommandeDocument"];
        $dateCommande = $champs["DateCommande"];
        $montant = $champs["Montant"];
        $nbExemplaire = $champs["NbExemplaire"];
        $etat = $champs["Etat"];
        $dateSuivi = $champs["DateSuivi"];

        $requete = "SELECT MAX(id) AS maxId FROM commande;";
        $maxId = $this->conn->queryBDD($requete);
        $maxId = intval($maxId[0]["maxId"] ?? 0) + 1;

        $requete = "INSERT INTO commande VALUES ($maxId, '$dateCommande', $montant);";
        $this->conn->updateBDD($requete);

        $requete = "INSERT INTO commandedocument VALUES ($maxId, $nbExemplaire, '$id');";
        $this->conn->updateBDD($requete);

        $requete = "INSERT INTO suivi(etat, dateSuivi, idCommandeDocument) 
                    VALUES ('$etat', '$dateSuivi', '$maxId');";
        $this->conn->updateBDD($requete);

        return $maxId;
    }
    
    private function insertNewAbonnement(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        
        $idRevue = $champs["IdRevue"];
        $dateFinAbonnement = $champs["DateFinAbonnement"];
        $dateCommande = $champs["DateCommande"];
        $montant = $champs["Montant"];
        
        $requete = "SELECT MAX(id) AS maxId FROM commande;";
        $maxId = $this->conn->queryBDD($requete);
        $maxId = intval($maxId[0]["maxId"] ?? 0) + 1;
        
        $requete = "INSERT INTO commande VALUES ('$maxId', '$dateCommande','$montant');";
        $this->conn->updateBDD($requete);
        
        $requete = "INSERT INTO abonnement VALUES ('$maxId', '$dateFinAbonnement', '$idRevue');";
        $this->conn->updateBDD($requete);
        
        return $maxId;
    }

    private function updateSuivi(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        
        $idCommande = $champs["IdCommande"];
        $idSuivi = $champs["Id"];
        $montant = $champs["Montant"];
        $nbExemplaire = $champs["NbExemplaire"];
        $etat = $champs["Etat"];
        $dateSuivi = $champs["DateSuivi"];
        
        $requete = "UPDATE commande SET montant = $montant WHERE id = $idCommande;";
        $this->conn->updateBDD($requete);
        
        $requete = "UPDATE commandedocument SET nbExemplaire = $nbExemplaire WHERE id = $idCommande;";
        $this->conn->updateBDD($requete);
        
        $requete = "UPDATE suivi SET etat = '$etat', dateSuivi = '$dateSuivi' WHERE id = $idSuivi;";
        return $this->conn->updateBDD($requete);
    }
    
    private function updateAbonnement(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        
        $dateFinAbonnement = $champs["DateFinAbonnement"];
        $montant = $champs["Montant"];
        $idCommande = $champs["Id"];
        
        $requete = "UPDATE commande SET montant = $montant WHERE id = $idCommande;";
        $this->conn->updateBDD($requete);
        
        $requete = "UPDATE abonnement set dateFinAbonnement = '$dateFinAbonnement' WHERE id = $idCommande;";
        return $this->conn->updateBDD($requete);
    }

    private function deleteSuivi(?array $champs) :?int
    {
        
        if(empty($champs)){
            return null;
        }
        
        $idCommande = $champs["id"];
        
        $requete = "DELETE FROM suivi WHERE idCommandeDocument = $idCommande";
        $this->conn->updateBDD($requete);
        
        $requete = "DELETE FROM commandedocument WHERE id = $idCommande";
        $this->conn->updateBDD($requete);
        
        $requete = "DELETE FROM commande WHERE id = $idCommande";
        return $this->conn->updateBDD($requete);
    }
    
    private function deleteAbonnement(?array $champs) :?int{
        if(empty($champs)){
            return null;
        }
        
        $idCommande = $champs["id"];
        
        $requete = "DELETE FROM abonnement WHERE id = $idCommande";
        $this->conn->updateBDD($requete);
        
        $requete = "DELETE FROM commande WHERE id = $idCommande";
        return $this->conn->updateBDD($requete);
    }
    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array{
        $requete = "select * from $table order by libelle;";		
        return $this->conn->queryBDD($requete);	    
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array{
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";		
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array{
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";	
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array{
        $requete = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère tous les exemplaires d'une revue
     * @param array|null $champs 
     * @return array|null
     */
    private function selectExemplairesRevue(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }		    
    
    private function selectAllSuivi(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "SELECT ";
        $requete .= "s.id, s.etat, s.dateSuivi, s.idCommandeDocument, ";
        $requete .= "cd.nbExemplaire, cd.idLivreDvd, ";
        $requete .= "c.id AS idCommande, c.dateCommande, c.montant ";
        $requete .= "FROM commandedocument cd ";
        $requete .= "JOIN suivi s ON s.idCommandeDocument = cd.id ";
        $requete .= "JOIN commande c ON cd.id = c.id ";
        $requete .= "WHERE cd.idLivreDvd = :id ";
        $requete .= "ORDER BY c.dateCommande;";

        return $this->conn->queryBDD($requete, $champNecessaire);
    }

    private function selectAllAbonnement(?array $champs) : ?array{

        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "SELECT ";
        $requete .= "c.id, c.dateCommande, c.montant, ";
        $requete .= "a.dateFinAbonnement, a.idRevue ";
        $requete .= "FROM abonnement a ";
        $requete .= "JOIN commande c ON a.id = c.id ";
        $requete .= "WHERE a.idRevue = :id ";
        $requete .= "ORDER BY c.dateCommande;";
        
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    private function selectAllFinAbonnement() : ?array{
        
        $requete = "SELECT ";
        $requete .= "a.dateFinAbonnement, d.titre ";
        $requete .= "FROM abonnement a ";
        $requete .= "JOIN document d ON d.id = a.idRevue ";
        $requete .= "WHERE dateFinAbonnement <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ";
        $requete .= "ORDER BY dateFinAbonnement ASC;";
        
        return $this->conn->queryBDD($requete);
    }
}
