<?php
class medicament
{
    private $id;
    private $nom;
    private $prix;
    private $stock;
    private $id_pharmacie;
    private $images;    

    public function __construct($id, $nom, $prix, $stock, $categorie, $images, $id_pharmacie)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->categorie = $categorie;
        $this->images = $images;
        $this->prix = $prix;
        $this->stock = $stock;
        $this->id_pharmacie = $id_pharmacie;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function getPrix()
    {
        return $this->prix;
    }

        public function getStock()
        {
            return $this->stock;
        }
        public function getCategorie()
        {
            return $this->categorie;
        }
        public function getImages()
        {
            return $this->images;
        }

        public function getIdPharmacie()
        {
            return $this->id_pharmacie;
        }
    }