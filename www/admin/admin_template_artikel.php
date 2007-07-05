<?php
########################################
#### explanation of editor creation ####
########################################
/*
    $TEMPLATE_GO = ""; //$_GET-variable "go", important to stay at the same page ;)

    $TEMPLATE_EDIT[0][name] = "name"; //name of the template's db-entry
    $TEMPLATE_EDIT[0][title] = "title"; //title of the template
    $TEMPLATE_EDIT[0][description] = "description"; //short description of what the template is for
    $TEMPLATE_EDIT[0][rows] = "x"; //number of rows of the textarea
    $TEMPLATE_EDIT[0][cols] = "y"; //number of cols of the textarea
        $TEMPLATE_EDIT[0][help][0][tag] = "{tag}"; //{tag}s which may be used in the template
        $TEMPLATE_EDIT[0][help][0][text] = "text"; //description of the tag, shown at the tooltip
        $TEMPLATE_EDIT[0][help][...][tag] = "{tag}"; //continue with numbers after [help]
        $TEMPLATE_EDIT[0][help][...][text] = "text"; //to add more possible tags

    $TEMPLATE_EDIT[1] = false; //creates a vertcal bar to separate templates

    $TEMPLATE_EDIT[...][name] = "..."; //continue with the numbers after $TEMPLATE_EDIT to add more template-editors
    ...
*/
##########################################
#### / explanation of editor creation ####
##########################################

    $TEMPLATE_GO = "artikeltemplate";

    $TEMPLATE_EDIT[0][name] = "artikel_autor";
    $TEMPLATE_EDIT[0][title] = "Autor";
    $TEMPLATE_EDIT[0][description] = "Das Erscheinungsbild der Autoren-Kennzeichnung.";
    $TEMPLATE_EDIT[0][rows] = "4";
    $TEMPLATE_EDIT[0][cols] = "66";
        $TEMPLATE_EDIT[0][help][0][tag] = "{username}";
        $TEMPLATE_EDIT[0][help][0][text] = "Der Username des Autors.";
        $TEMPLATE_EDIT[0][help][1][tag] = "{profillink}";
        $TEMPLATE_EDIT[0][help][1][text] = "URL zum Profil des Autors.";
        

    $TEMPLATE_EDIT[1][name] = "artikel_body";
    $TEMPLATE_EDIT[1][title] = "Body";
    $TEMPLATE_EDIT[1][description] = "Das Erscheinungsbild eines Artikels.";
    $TEMPLATE_EDIT[1][rows] = "25";
    $TEMPLATE_EDIT[1][cols] = "66";
        $TEMPLATE_EDIT[1][help][0][tag] = "{titel}";
        $TEMPLATE_EDIT[1][help][0][text] = "Die �berschrift des Artikels.";
        $TEMPLATE_EDIT[1][help][1][tag] = "{datum}";
        $TEMPLATE_EDIT[1][help][1][text] = "Das Datum an dem der Artikel geschrieben wurde.";
        $TEMPLATE_EDIT[1][help][2][tag] = "{text}";
        $TEMPLATE_EDIT[1][help][2][text] = "Der Artikel-Text an sich.";
        $TEMPLATE_EDIT[1][help][2][tag] = "{autor}";
        $TEMPLATE_EDIT[1][help][2][text] = "Bindet das Autoren-Template ein.";

        
//////////////////////////
//// Intialise Editor ////
//////////////////////////

if (templatepage_postcheck($TEMPLATE_EDIT))
{
    templatepage_save($TEMPLATE_EDIT);
    systext("Template wurde aktualisiert");
}
else
{
    echo create_templatepage ($TEMPLATE_EDIT, $TEMPLATE_GO);
}
?>