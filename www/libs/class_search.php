<?php
/**
 * @file     class_search.php
 * @folder   /libs
 * @version  0.2
 * @author   Sweil
 *
 * this class is responsible for search operations
 */
class Search {

    // Set Class Vars
    protected $theConfig;
    protected $theGlobalConfig;
    protected $theSql;
    private $searchTypeArray;
    private $searchTypeArraySQL = array ();
    
    /**
     * Creates an object for specified Search Type
     *
     * @name sql::__construct();
     *
     * @param String $searchType
     *
     * @return bool
     */
    public function  __construct ( $searchTypeArray = array() ) {
        // Include global Data
        global $global_config_arr, $sql;
        $this->theGlobalConfig = $global_config_arr;
        $this->theSql = $sql;
        $this->theConfig = $this->theSql->getData ( "search_config", "*", "", 1 );
        $this->searchTypeArray = $searchTypeArray;
        foreach ( $this->searchTypeArray as $aType ) {
            $this->searchTypeArraySQL[] = str_replace ( '$1', $aType, "FIND_IN_SET('\$1',I.`search_index_type`)" );
        }
    }

    // check for search opertors
    private function isSearchOp ( $CHECK, $OP_ARR = array ( "AND", "OR", "NOT", "XOR" ) ) {
        return in_array ( $CHECK, $OP_ARR );
    }
    
    private function compressKeyword ( $TEXT ) {
        if ( $this->isSearchOp ( $TEXT ) ) {
            return $TEXT;
        }
        return $this->removeStopwords ( $this->compressSearchData ( $TEXT ) );
    }
    
    private function createSearchWordArray ( $ARR, $DEFAULT = "AND" ) {
        $ARR = (array) $ARR;
        $ARR = array_filter ( array_map ( "compressKeyword", $ARR ), "strlen" );

        if ( count ( $ARR ) > 1 ) {
            $search_word_arr = array ();
            $before_op = TRUE;
            foreach ( $ARR as $word ) {
                if ( $this->isSearchOp ( $word ) && $before_op !== TRUE ) {
                    $search_word_arr[] = $word;
                    $before_op = TRUE;
                } elseif ( $before_op === TRUE && !$this->isSearchOp ( $word ) ) {
                    $search_word_arr[] = $word;
                    $before_op = FALSE;
                } elseif ( !$this->isSearchOp ( $word ) )  {
                    $search_word_arr[] = $DEFAULT;
                    $search_word_arr[] = $word;
                    $before_op = FALSE;
                }
            }
            if ( $this->isSearchOp ( end ( $search_word_arr ) ) ) {
                array_pop ( $search_word_arr );
            }
            return $search_word_arr;
        } else {
            return $ARR;
        }
    }
    
    private function compareFoundsWithKeywords ( $FOUNDS, $KEYWORDS ) {
        $bool_arr = array ();
        foreach ( $KEYWORDS as $word ) {
            if ( !$this->isSearchOp ( $word ) ) {
                $bool_arr[] = ( array_key_exists ( $word, $FOUNDS ) ) ? "TRUE" : "FALSE";
            } else {
                switch ( $word ) {
                    case "NOT":
                        $bool_arr[] = " && !";
                        break;
                    case "OR":
                        $bool_arr[] = " || ";
                        break;
                    case "AND":
                        $bool_arr[] = " && ";
                        break;
                    case "XOR":
                        $bool_arr[] = " xor ";
                        break;
                }
            }
        }
        $bool_return = FALSE;
        $bool_string = implode ( "", $bool_arr );
        eval ("\$bool_return = (".$bool_string.");" );
        if ( $bool_return ) {
            return TRUE;
        }
        return  FALSE;
    }

    function getIdList ( $RESULTS_ARR, $SOFT_MAX_SELECT ) {
        // Security Function
        $results_id_list = array();
        $counter_of_last_found = -1;

        // Sort Array by num of founds
        asort ( $RESULTS_ARR, SORT_NUMERIC );
        $RESULTS_ARR = array_reverse ( $RESULTS_ARR, TRUE );
        reset ( $RESULTS_ARR );

        // Get List of IDs to select
        for ( $i = 0; $i < $SOFT_MAX_SELECT || ( current ( $RESULTS_ARR ) == $counter_of_last_found ) ; $i++ ) {
            $id = key ( $RESULTS_ARR );
            $results_id_list[] = $id;
            if ( $i == ( $SOFT_MAX_SELECT - 1 ) ) {
                $counter_of_last_found = current ( $RESULTS_ARR );
            }
            next ( $RESULTS_ARR );
        }

        if ( count ( $results_id_list ) <= 0 ) {
            $results_id_list = array ( -100 );
        }
        return $results_id_list;
    }
    
    function sortReplaceArr ( $REPLACE_ARR ) {
        foreach ( $REPLACE_ARR as $key => $row ) {
            $col_date[$key] = $row['date'];
            $col_num_results[$key] = $row['num_results'];
        }
        array_multisort ( (array)$col_num_results, SORT_DESC, SORT_NUMERIC, (array)$col_date, SORT_DESC, SORT_NUMERIC, $REPLACE_ARR );
        return $REPLACE_ARR;
    }
    


    // make a search
    public function makeSearch ( $KEYWORDS ) {
        // create keywords
        $keyword_arr = explode ( " ", $KEYWORDS );
        $keyword_arr = $this->createSearchWordArray ( $keyword_arr );
        // Get Special SQL-Query for Types
        $type_check = ( count ( $this->searchTypeArraySQL ) > 0 ) ? "AND ( " . implode ( " OR ", $this->searchTypeArraySQL ) . " )" : "";
        
        // Get Founds from Index
        $founds_arr = array ();
        foreach ( $keyword_arr as $word ) {
            if ( !$this->isSearchOp ( $word ) ) {
                $search = $this->theSql->query ( "
                    SELECT
                            I.`search_index_document_id` AS 'document_id',
                            I.`search_index_type` AS 'type',
                            SUM(I.`search_index_count`) AS 'count'

                    FROM
                            `{..pref..}search_index` AS I,
                            `{..pref..}search_words` AS W

                    WHERE   1
                            " . $type_check  . "
                            AND W.`search_word_id` = I.`search_index_word_id`
                            AND W.`search_word` LIKE '%".$word."%'

                    GROUP BY `document_id`, `type`
                    ORDER BY `type`, `count` DESC
                " );

                while ( $data_arr = mysql_fetch_assoc ( $search ) ) {
                    $founds_arr[$data_arr['type']][$data_arr['document_id']][$word] = $data_arr['count'];
                }
            }
        }
        
        
        $results_arr = array ();
        foreach ( $founds_arr as $type => $docs ) {
            foreach ( $docs as $id => $founds ) {
                if ( $this->compareFoundsWithKeywords ( $founds, $keyword_arr ) ) {
                    $results_arr[$type][$id] = array_sum ( $founds );
                }
            }
        }

    }
    
    
    protected function compressSearchData ( $TEXT ) {
        $locSearch[] = "=�=i";
        $locSearch[] = "=�|�=i";
        $locSearch[] = "=�|�=i";
        $locSearch[] = "=�|�=i";
        $locSearch[] = "=�|�|�|�|�|�=i";
        $locSearch[] = "=�|�|�|�|�|�=i";
        $locSearch[] = "=�|�|�|�|�|�=i";
        $locSearch[] = "=�|�|�|�|�|�|�=i";
        $locSearch[] = "=�|�|�|�|�|�|�=i";
        $locSearch[] = "=�=i";
        $locSearch[] = "=�=i";
        $locSearch[] = "=([^A-Za-z0-9])=";
        $locSearch[] = "= +=";
        #$locSearch[] = "=([0-9/.,+-]*\s)=";

        $locReplace[] = "ss";
        $locReplace[] = "ae";
        $locReplace[] = "oe";
        $locReplace[] = "ue";
        $locReplace[] = "a";
        $locReplace[] = "o";
        $locReplace[] = "u";
        $locReplace[] = "e";
        $locReplace[] = "i";
        $locReplace[] = "n";
        $locReplace[] = "c";
        $locReplace[] = " ";
        $locReplace[] = " ";
        #$locReplace[] = " ";

        $TEXT = trim ( strtolower ( stripslashes ( killfs ( $TEXT ) ) ) );
        $TEXT = preg_replace ( $locSearch, $locReplace, $TEXT );
        return $TEXT;
    }

    protected function removeStopwords ( $TEXT ) {
        $locSearch = array ();
        $locReplace = array ();
        
        $locSearch[] = "=(\s[A-Za-z0-9]{1,".$this->theConfig['search_min_length']."})\s=";
        $locReplace[] = " ";
        
        // Use Stopwords?
        if ( $this->theConfig['	search_use_stopwords'] == 1 ) {
            $locSearch[] = "= " . implode ( " | ", $this->getStopwords () ) . " =i";
            $locReplace[] = " ";
        }
        
        $locSearch[] = "= +=";
        $locReplace[] = " ";

        $TEXT = " " . str_replace ( " ", "  ", $TEXT ) . " ";
        $TEXT = trim ( preg_replace ( $locSearch, $locReplace, $TEXT ) );
        return $TEXT;
    }

    protected function getStopwords () {
        $stopfilespath =  FS2_ROOT_PATH . "resources/stopwords/";
        $stopfiles = scandir_ext ( $stopfilespath, ".txt" );
        $ACCESS = new fileaccess();
        
        $return_arr = array ();
        foreach ( $stopfiles as $file ) {
            $return_arr = array_merge ( $return_arr, $ACCESS->getFileArray( $stopfilespath.$file ) );
        }
        return $return_arr;
    }
}
?>