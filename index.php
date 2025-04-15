<?php 

    require_once($_SERVER["DOCUMENT_ROOT"] . "/classes/tree.class.php");

    $tree = new Tree();
    
    $tree->Draw($tree->Get());
    print_r($tree->GetPlain());
    
?>