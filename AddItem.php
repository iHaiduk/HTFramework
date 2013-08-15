<?php
/**
 * Created by JetBrains PhpStorm.
 * User: developer-18
 * Date: 14.08.13
 * Time: 18:03
 * To change this template use File | Settings | File Templates.
 */

class randomBase extends RestModule{

    function run(){

        $array_name = array("Cloth Rimes", "Meatsteak Cahoonas", "Space Fuel", "Stott Spices", "BoFu", "Massom Powder",
            "Rastar Oil", "Majaglit", "Soja Husk", "Nostrop Oil", "Space Weed", "Terran MRE",
            "Debris", "Artefacts", "Artificial Fertilizer", "Biological Micro-Organisms", "Cartography Chips", "Construction Equipment",
            "Engine Components", "Food Rations", "Hand Weapons", "Luxury Foodstuffs", "Fish", "Meat",
            "Hazardous Waste", "Shells", "Vegetables", "Fruit", "Engine Components", "Seeds");
        ///$array_name2 = array("Pupko", "Galisin", "Forot", "Volikin", "Neponyl", "Tryskaves");
        $text = array("It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.",
            "The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters,",
            "as opposed to using 'Content here, content here', making it look like readable English.",
            "Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy.",
            "Various versions have evolved over the years, sometimes by accident,",
            "sometimes on purpose (injected humour and the like).",
            "Nunc vestibulum sem eu eleifend placerat.",
            "Sed a augue mauris. Nullam fermentum, eros et vestibulum vestibulum, nibh lorem hendrerit elit, a ornare purus purus quis turpis.",
            "Nam elementum accumsan diam, eu posuere dolor sagittis vitae.",
            "Vestibulum hendrerit interdum feugiat. Fusce sed nunc et velit lacinia malesuada ut nec nunc.",
            "Nullam ac gravida odio, eget pellentesque orci.",
            "Nulla pretium odio at tincidunt vulputate.");

        $partner = array("NEWS TRAVEL", "MOUZENIDIS TRAVEL", "GTO TRAVEL", "TURTESS TRAVEL", "TEZ TOUR", "TEZ TOUR2");


        $project1 = array("My ", "Your ", "Test ", "None ");


        for($i=0; $i<1; $i++){
        $base = new \Entities\Item();
        $base->setName(htmlspecialchars($array_name[mt_rand(0,29)]." -- ".$array_name[mt_rand(0,29)]."  ".$i));
            $rand_id = mt_rand(15,30);
        $base->setCategory($this->EM->getRepository('Entities\Category')->findOneById($rand_id));
        $base->setDescription(htmlspecialchars($text[mt_rand(0,11)]));
            if(mt_rand(0,10)>7) $base->setUnique(1); else $base->setUnique(0);
            $base->setQuantity(mt_rand(0,200));

        $base->setAvatar('http://s05.radikal.ru/i178/1009/85/f6e6bb0c53df.jpg');
        var_dump($base, $rand_id);
        $this->EM->persist($base);
        }

        $this->EM->flush();


    }

}
