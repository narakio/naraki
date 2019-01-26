<?php

class Product
{

    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $brands;
    /**
     * @var array
     */
    private $categoryIndex = [];
    /**
     * @var array
     */
    private $categories;
    /**
     * @var string
     */
    private $embCode;
    /**
     * @var string
     */
    private $ingredientsText;
    /**
     * @var array
     */
    private $ingredients;
    /**
     * @var int
     */
    private $novaGroup;
    /**
     * @var $array
     */
    private $nutrientLevels;
    /**
     * @var array
     */
    private $nutriments;
    /**
     * @var string
     */
    private $nutritionGrade;
    /**
     * @var array
     */
    private $packaging;
    /**
     * @var Image
     */
    private $image;

    /**
     * @param int $novaGroup
     */
    public function setNovaGroup(int $novaGroup)
    {
        $this->novaGroup = $novaGroup;
    }

    /**
     * @param array $nutrientLevels
     */
    public function setNutrientLevels($nutrientLevels)
    {
        $this->nutrientLevels = $nutrientLevels;
    }

    /**
     * @param $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return Image
     */
    public function getImage(): Image
    {
        return $this->image;
    }


    /**
     * @return string
     */
    public function getEmbCode(): string
    {
        return $this->embCode;
    }

    /**
     * @param Packager $embCode
     */
    public function setEmbCode(Packager $embCode)
    {
        $this->embCode = $embCode;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     * @throws \Exception
     */
    public function setCategories(array $categories)
    {
        foreach ($categories as $category) {
            if (!isset($this->categoryIndex[$category[1]]) && !empty($this->categoryIndex[$category[1]])) {
                $this->categoryIndex[$category[1]] = true;
                $this->categories[] = new ProductCategory($category[1], $category[0]);
            }
        }
    }

    public function unsetCategoryIndex()
    {
        $this->categoryIndex = null;
    }

    public function setIngredientsText($ingredients)
    {
        $this->ingredientsText = $ingredients;

    }

    public function setIngredients($ingredients)
    {
        $this->ingredients = $ingredients;
    }

    /**
     * @return array
     */
    public function getBrands(): array
    {
        return $this->brands;
    }

    /**
     * @param array $brands
     */
    public function setBrands(array $brands)
    {
        foreach ($brands as $brand) {
            $this->brands[] = new Brand($brand);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = ucfirst($name);
    }

    public function __set($name, $value)
    {
        $this->name = $value;
    }

    /**
     * @param array $nutriments
     */
    public function setNutriments(array $nutriments)
    {
        $this->nutriments = $nutriments;
    }

    /**
     * @param string $nutritionGrade
     */
    public function setNutritionGrade(string $nutritionGrade)
    {
        $this->nutritionGrade = $nutritionGrade;
    }

    /**
     * @param array $packaging
     */
    public function setPackaging(array $packaging)
    {
        $this->packaging = $packaging;
    }


}