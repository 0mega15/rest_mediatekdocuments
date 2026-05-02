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
            case "exemplaireglobal" :
                return $this->selectExemplairesType($champs);
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
            case "connexion" :
                return $this->selectAllConnexion($champs);
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
            case "livre" :
                return $this->insertNouveauLivre($champs);
            case "dvd" :
                return $this->insertNouveauDvd($champs);
            case "revue" :
                return $this->insertNouveauRevue($champs);
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
            case "livre" :
                return $this->updateLivre($champs);
            case "dvd" :
                return $this->updateDvd($champs);
            case "revue" :
                return $this->updateRevue($champs);
            case "exemplaireglobal":
                return $this->updateExemplaireType($champs);
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
            case "livre" :
                return $this->deleteLivre($champs);
            case "dvd" :
                return $this->deleteDvd($champs);
            case "revue" :
                return $this->deleteRevue($champs);   
            case "exemplaireglobal" :
                return $this->deleteExemplaireType($champs);
            default:                    
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }	    
    
    /**
     * demande d'ajout (insert) d'un livre, en ajoutant les données table par table.
     * @param array $livre
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    public function insertNouveauLivre(array $livre): ?int {
        try {
            ///Insérer dans document
            $documentData = [
                'id' => $livre['Id'],
                'titre' => $livre['Titre'],
                'image' => $livre['Image'] ?? '',
                'idRayon' => $livre['IdRayon'],
                'idPublic' => $livre['IdPublic'],
                'idGenre' => $livre['IdGenre']
            ];
            $this->insertOneTupleOneTable('document', $documentData);

            ///Insérer dans livres_dvd
            $this->insertOneTupleOneTable('livres_dvd', ['id' => $livre['Id']]);

            ///Insérer dans livre
            $livreData = [
                'id' => $livre['Id'],
                'ISBN' => $livre['Isbn'],
                'auteur' => $livre['Auteur'],
                'collection' => $livre['Collection'] ?? ''
            ];
            return $this->insertOneTupleOneTable('livre', $livreData);
        } catch (Exception $e) {
            error_log("Erreur : " . $e->getMessage());
            return null;
        }
    }
        /**
         * demande d'ajout (insert) d'un dvd, en ajoutant les données table par table.
         * @param array $dvd
         * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
         */	
    public function insertNouveauDvd(array $dvd): ?int {
        try {
            ///Insérer dans document
            $documentData = [
                'id' => $dvd['Id'],
                'titre' => $dvd['Titre'],
                'image' => $dvd['Image'] ?? '',
                'idRayon' => $dvd['IdRayon'],
                'idPublic' => $dvd['IdPublic'],
                'idGenre' => $dvd['IdGenre']
            ];
            $this->insertOneTupleOneTable('document', $documentData);

            ///Insérer dans livres_dvd
            $this->insertOneTupleOneTable('livres_dvd', ['id' => $dvd['Id']]);

            ///Insérer dans dvd
            $dvdData = [
                'id' => $dvd['Id'],
                'duree' => $dvd['Duree'],
                'realisateur' => $dvd['Realisateur'],
                'synopsis' => $dvd['Synopsis'] ?? ''
            ];
            return $this->insertOneTupleOneTable('dvd', $dvdData);
        } catch (Exception $e) {
            error_log("Erreur : " . $e->getMessage());
            return null;
        }
    }
   /**
    * demande d'ajout (insert) d'une revue, en ajoutant les données table par table.
    * @param array $revue
    * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
    */	
    public function insertNouveauRevue(array $revue): ?int {
        try {
            ///Insérer dans document
            $documentData = [
                'id' => $revue['Id'],
                'titre' => $revue['Titre'],
                'image' => $revue['Image'] ?? '',
                'idRayon' => $revue['IdRayon'],
                'idPublic' => $revue['IdPublic'],
                'idGenre' => $revue['IdGenre']
            ];
            $this->insertOneTupleOneTable('document', $documentData);

            ///Insérer dans revues
            $revueData = [
                'id' => $revue['Id'],
                'periodicite' => $revue['Periodicite'],
                'delaiMiseADispo' => $revue['DelaiMiseADispo']
            ];
            return $this->insertOneTupleOneTable('revue', $revueData);
        } catch (Exception $e) {
            error_log("Erreur : " . $e->getMessage());
            return null;
        }
    }
    /**
     * Récupère tous les exemplaires d'un élément typé précis
     * @param array|null $champs
     * @return array|null
     */
    private function selectExemplairesType(?array $champs) : ?array{
        if (empty($champs) || !array_key_exists('id', $champs)) {
            return null;
        }

        $idDocument = $champs['id'];
        $requete = "
            SELECT
                e.id,
                e.numero,
                e.dateAchat,
                e.photo,
                e.idEtat,
                et.libelle AS libelleEtat
            FROM
                exemplaire e
            JOIN
                etat et ON e.idEtat = et.id
            JOIN
                document d ON e.id = d.id
            WHERE
                e.id = :idDocument
            ORDER BY
                e.numero;
        ";
        $params = ['idDocument' => $idDocument];
        return $this->conn->queryBDD($requete, $params);
    }
    /*
     * Met à jour le type d'un exemplaire précis et donné.
     * @param array|null $champs
     * @return array|null
     */
    private function updateExemplaireType($champs) : ?int{
        error_log("updateExemplaireType appelée avec : " . print_r($champs, true));
        if (empty($champs)) {
            return null;
        }

        
        $requete = "
            UPDATE exemplaire
            SET
                idEtat = :idEtat
            WHERE
                id = :id AND numero = :numero;
        ";

        $params = [
            'id' => $champs['Id'],
            'numero' => $champs['Numero'],
            'idEtat' => $champs['IdEtat']
        ];

        return $this->conn->updateBDD($requete, $params); 
    }
    
    /*
     * Supprime un exemplaire précis et donné.
     * @param array|null $champs
     * @return array|null
     */
    private function deleteExemplaireType(array $champs) : ?int{
    $requete = "
        DELETE FROM exemplaire
        WHERE
            id = :id AND numero = :numero;
    ";
    $params = [
        'id' => $champs['Id'],
        'numero' => $champs['Numero']   
    ];
        return $this->conn->updateBDD($requete, $params); 
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
    
    /*
     * Ajoute un nouveau suivi de commande.
     * @param array|null $champs
     */
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
     /*
     * Ajoute un nouvel abonnement.
     * @param array|null $champs
     */
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
     /*
     * Met à jour un suivi de commande.
     * @param array|null $champs
     */
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
     /*
     * Met à jour un abonnement.
     * @param array|null $champs
     */
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
     /*
     * Supprime un suivi de commande.
     * @param array|null $champs
     */
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
     /*
     * Supprime un abonnement.
     * @param array|null $champs
     */
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
     * demande de modification (update) d'un livre, en ajoutant les données table par table.
     * @param array $livre
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	    
public function updateLivre(array $livre): ?int{
    try {
        ///Insérer dans document
        $documentData = [
            'titre' => $livre['Titre'],
            'image' => $livre['Image'] ?? '',
            'idRayon' => $livre['IdRayon'],
            'idPublic' => $livre['IdPublic'],
            'idGenre' => $livre['IdGenre']
        ];
        $this->updateOneTupleOneTable('document', $livre['Id'], $documentData);

        ///Insérer dans livre
        $livreData = [
            'id' => $livre['Id'],
            'ISBN' => $livre['Isbn'],
            'auteur' => $livre['Auteur'],
            'collection' => $livre['Collection'] ?? ''
        ];
        return $this->updateOneTupleOneTable('livre', $livre['Id'], $livreData);
    } catch (Exception $e) {
        error_log("Erreur : " . $e->getMessage());
        return null;
    }    
}
    /**
     * demande de modification (update) d'un dvd, en ajoutant les données table par table.
     * @param array $dvd
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	    
public function updateDvd(array $dvd): ?int{
    try {
        ///Insérer dans document
        $documentData = [
            'titre' => $dvd['Titre'],
            'image' => $dvd['Image'] ?? '',
            'idRayon' => $dvd['IdRayon'],
            'idPublic' => $dvd['IdPublic'],
            'idGenre' => $dvd['IdGenre']
        ];
        $this->updateOneTupleOneTable('document', $dvd['Id'], $documentData);

        ///Insérer dans dvd
        $dvdData = [
            'id' => $dvd['Id'],
            'duree' => $dvd['Duree'],
            'realisateur' => $dvd['Realisateur'],
            'synopsis' => $dvd['Synopsis'] ?? ''
        ];
        return $this->updateOneTupleOneTable('dvd', $dvd['Id'], $dvdData);
    } catch (Exception $e) {
        error_log("Erreur : " . $e->getMessage());
        return null;
    }
}
    /**
     * demande de modification (update) d'une revue, en ajoutant les données table par table.
     * @param array $revue
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	    
public function updateRevue(array $revue): ?int{
    try {
        ///Insérer dans document
        $documentData = [
            'titre' => $revue['Titre'],
            'image' => $revue['Image'] ?? '',
            'idRayon' => $revue['IdRayon'],
            'idPublic' => $revue['IdPublic'],
            'idGenre' => $revue['IdGenre']
        ];
        $this->updateOneTupleOneTable('document', $revue['Id'], $documentData);

        ///Insérer dans revue
        $revueData = [
            'id' => $revue['Id'],
            'periodicite' => $revue['Periodicite'],
            'delaiMiseADispo' => $revue['DelaiMiseADispo']
        ];
        return $this->updateOneTupleOneTable('revue', $revue['Id'], $revueData);
    } catch (Exception $e) {
        error_log("Erreur : " . $e->getMessage());
        return null;
    }
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
     * demande de suppression (delete) d'un livre.
     * @param array $livre
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    public function deleteLivre(array $livre): ?int{
    try {
        if (!isset($livre['Id'])) {
            error_log("Erreur : ID du livre manquant.");
            return null;
        }
        $id = $livre['Id'];

        $resultLivre = $this->deleteTuplesOneTable('livre', ['id' => $id]);
        if ($resultLivre === null) {
            return null;
        }

        $resultLivresDvd = $this->deleteTuplesOneTable('livres_dvd', ['id' => $id]);
        if ($resultLivresDvd === null) {
            return null;
        }

        $resultDocument = $this->deleteTuplesOneTable('document', ['id' => $id]);
        if ($resultDocument === null) {
            return null;
        }

        return $resultDocument;
    } catch (Exception $e) {
        error_log("Erreur dans deleteLivre : " . $e->getMessage());
        return null;
    }
}
    /**
     * demande de suppression (delete) d'un dvd.
     * @param array $dvd
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
public function deleteDvd(array $dvd): ?int{
    try {
        if (!isset($dvd['Id'])) {
            error_log("Erreur : ID du dvd manquant.");
            return null;
        }
        $id = $dvd['Id'];

        $resultDvd = $this->deleteTuplesOneTable('dvd', ['id' => $id]);
        if ($resultDvd === null) {
            return null;
        }

        $resultLivresDvd = $this->deleteTuplesOneTable('livres_dvd', ['id' => $id]);
        if ($resultLivresDvd === null) {
            return null;
        }

        $resultDocument = $this->deleteTuplesOneTable('document', ['id' => $id]);
        if ($resultDocument === null) {
            return null;
        }

        return $resultDocument;
    } catch (Exception $e) {
        error_log("Erreur dans deleteDvd : " . $e->getMessage());
        return null;
    }
}
    /**
     * demande de suppression (delete) d'une revue.
     * @param array $revue
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
public function deleteRevue(array $revue): ?int{
    try {
        if (!isset($revue['Id'])) {
            error_log("Erreur : ID de la revue manquant.");
            return null;
        }
        $id = $revue['Id'];

        $resultRevue = $this->deleteTuplesOneTable('revue', ['id' => $id]);
        if ($resultRevue === null) {
            return null;
        }

        $resultDocument = $this->deleteTuplesOneTable('document', ['id' => $id]);
        if ($resultDocument === null) {
            return null;
        }

        return $resultDocument;
    } catch (Exception $e) {
        error_log("Erreur dans deleteRevue : " . $e->getMessage());
        return null;
    }
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
    
    private function selectAllConnexion(?array $champs) : ?array{
        
        $data = $champs["data"];
        $login = $data["login"];
        $password = $data["password"];
        $requete = "SELECT * FROM utilisateur WHERE login = '$login' AND password = '$password'";
        return $this->conn->queryBDD($requete);
    }
}