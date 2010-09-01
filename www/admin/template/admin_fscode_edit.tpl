<!--DEF::overview-->            <table border="0" cellpadding="4" cellspacing="0" width="600">
              <tr>
                <td class="line" colspan="4">
                  <!--LANG::overview_topic-->
                </td>
              </tr>
              <tr>
                <td colspan="4" class="space"></td>
              </tr>
              <tr>
                <td class="config"><!--LANG::overview_table_name--></td>
                <td class="config"><!--LANG::overview_table_added--></td>
                <td class="config"><!--LANG::overview_table_edited--></td>
                <td class="config"><!--LANG::overview_table_action--></td>
              </tr>
<!--PHRASE::item-->
            </table><!--ENDDEF-->

<!--DEF::overview_item-->                  <tr>
                <td class="config"><!--PHRASE::name--></td>
                <td class="config">
                  <!--PHRASE::add_date-->
                  <!--LANG::overview_table_added_by-->
                  <a href="../?go=profil&amp;userid=<!--PHRASE::add_user_id-->" target="_blank">
                    <!--PHRASE::add_user_name-->
                  </a>
                </td>
                <td class="config">
                  <!--IF::edited?1--><!--PHRASE::edit_date-->
                  <!--LANG::overview_table_edited_by-->
                  <a href="../?go=profil&amp;userid=<!--PHRASE::edit_user_id-->" target="_blank">
                    <!--PHRASE::edit_user_name-->
                  </a><!--ELSE--><!--LANG::overview_table_edited_never--><!--ENDIF-->
                </td>
                <td class="config">
                  [<a href="?go=fscode_edit&amp;action=edit&amp;code=<!--PHRASE::name-->" target="_self"><!--LANG::overview_table_action_edit--></a>]
                  <br>
                  [<a href="?go=fscode_edit&amp;action=delete&amp;code=<!--PHRASE::name-->" target="_self" onclick="return confirm('FS Code wirklich l�schen?');"><!--LANG::overview_table_action_remove--></a>]
                </td>
              </tr>
<!--ENDDEF-->

<!--DEF::detail-->
          <form action="" method="post" enctype="multipart/form-data">
            <script type="text/javascript">
              <!--
                Array.prototype.contains = function(val){
                  for(var i = 0; i < this.length; i++)
                    if(this[i] == val)
                      return true;

                  return false;
                }

                var definedcodes = new Array(<!--PHRASE::codenames-->);
                function checkcode(){
                  if(definedcodes.contains($('#codename').val()))
                    $('#namehint_1').css("display", "table-row");
                  else if($('#codename').val().match(/[^a-zA-Z-_]+/) && $('#codename').val() != ""<!--IF::name?*--> && $('#codename').val()<!--ENDIF-->) {
                    $('#namehint_2').css("display", "table-row");
                  } else {
                    $('#namehint_1').css("display", "none");
                    $('#namehint_2').css("display", "none");
                  }
                }

                function setflag(valuefield, value){
                  var selectflag = "<!--LANG::detail_flag_choose-->";
                  var options = new Array();
                  var optionselect = 0;
                  var numflags = parseInt($('input#numflags').val());
                  value = parseInt(value);
                  switch(value){
                    case 0:
                      if(numflags == 1)
                        valuefield.innerHTML = selectflag;
                      else {
                        valuefield.parentNode.parentNode.removeChild(valuefield.parentNode);
                        $('input#numflags').val(numflags - 1);
                      }
                      return;
                    case 1:
                      options[0] = "true";
                      options[1] = "false";
                      break;
                    case 2:
                      options[0] = "BBCODE_CLOSETAG_FORBIDDEN";
                      options[1] = "BBCODE_CLOSETAG_OPTIONAL";
                      options[2] = "BBCODE_CLOSETAG_IMPLICIT";
                      options[3] = "BBCODE_CLOSETAG_IMPLICIT_ON_CLOSE_ONLY";
                      options[4] = "BBCODE_CLOSETAG_MUSTEXIST";
                      optionselect = 2;
                      break;
                    case 8:
                      options[0] = "true";
                      options[1] = "false";
                      optionselect = 1;
                      break;
                    case 7:
                      options[0] = "BBCODE_PARAGRAPH_ALLOW_BREAKUP";
                      options[1] = "BBCODE_PARAGRAPH_ALLOW_INSIDE";
                      options[2] = "BBCODE_PARAGRAPH_BLOCK_ELEMENT";
                      break;
                    default:
                      options[0] = "BBCODE_NEWLINE_PARSE";
                      options[1] = "BBCODE_NEWLINE_IGNORE";
                      options[2] = "BBCODE_NEWLINE_DROP";
                  }
                  var output = '<select name="flag[1][]" class="input_width">\n';
                  for(var i = 0; i < options.length; i++){
                    output += '<option value="'+i+'"';
                    if(i==optionselect)
                      output += " selected";
                    output += ">"+options[i]+"</option>\n";
                  }
                  valuefield.innerHTML = output;
                  newflag();
                }

                function newflag(){
                  var flag = document.createElement("tr");
                  var td1 = document.createElement("td");
                  var td2 = document.createElement("td");
                  td1.setAttribute("class", "config");
                  td2.setAttribute("class", "config");
                  td1.innerHTML = '<select name="flag[0][]" onchange="setflag(this.parentNode.parentNode.childNodes[1], this.value);">\n<option value="0"></option>\n<option value="1">case_sensitive</option>\n<option value="2">closetag</option>\n<option value="3">opentag.before.newline</option>\n<option value="4">opentag.after.newline</option>\n<option value="5">closetag.before.newline</option>\n<option value="6">closetag.after.newline</option>\n<option value="7">paragraph_type</option>\n<option value="8">paragraphs</option>\n</select>\n';
                  td2.innerHTML = 'W�hle ein Flag!';
                  flag.appendChild(td1);
                  flag.appendChild(td2);
                  $('tbody#flags').get(0).appendChild(flag);
                  $('input#numflags').val(parseInt($('input#numflags').val()) - 1);
                }
              //-->
            </script>
            <script src="<!--PHRASE::FSROOT-->resources/codemirror/js/codemirror.js" type="text/javascript"></script>
            <!-- CSS-Definitions for IE-Browsers -->
            <!--[if IE]>
                <style type="text/css">
                    .html-editor-list-popup {
                        margin-top:20px;
                    }
                    .html-editor-container-list {
                        z-index:1;
                    }
                </style>
            <![endif]-->

            <!-- CSS-Definitions for Non-JS-Editor -->
            <noscript>
                <style type="text/css">
                    .html-editor-row {
                        display:none;
                    }
                    .html-editor-row-header {
                        border:none;
                    }
                    .html-editor-path .html-editor-highlighter {
                        display:none;
                    }
                </style>
            </noscript>
            <input type="hidden" value="fscode_edit" name="go">
            <input type="hidden" value="1" name="numflags" id="numflags">
            <input type="hidden" value="<!--PHRASE::id-->" name="id" id="id">
            <table border="0" cellpadding="4" cellspacing="0" width="600">
              <tbody>
                <tr>
                  <td class="line" colspan="2">
                    <!--LANG::detail_head-->
                  </td>
                </tr>
                <tr>
                  <td colspan="2" class="space"></td>
                </tr>
                <tr>
                  <td class="config" valign="top">
                    <!--LANG::detail_name-->
                  </td>
                  <td class="config" valign="top">
                    <input type="text" name="name" onkeyup="checkcode();" id="codename" class="text input_width" value="<!--PHRASE::name-->">
                  </td>
                </tr>
                <tr id="namehint_1" style="display: none;">
                  <td colspan="2"  class="config" style="text-align: right;">
                    <!--LANG::detail_name_hint_1-->
                  </td>
                </tr>
                <tr id="namehint_2" style="display: none;">
                  <td colspan="2"  class="config" style="text-align: right;">
                    <!--LANG::detail_name_hint_2-->
                  </td>
                </tr>
                <tr>
                  <td class="config" valign="top">
                    <!--LANG::detail_contenttype-->
                  </td>
                  <td class="config" valign="top">
                    <input type="text" name="contenttype" id="contenttype" class="text input_width" value="<!--PHRASE::contenttype-->">
                  </td>
                </tr>
                <tr>
                  <td class="config" valign="top">
                    <!--LANG::detail_allowin-->
                  </td>
                  <td class="config" valign="top">
                    <input type="text" name="allowin" value="<!--PHRASE::allowin-->" id="allowedin" class="text input_width">
                  </td>
                </tr>
                <tr>
                  <td class="config" valign="top">
                     <!--LANG::detail_disallowin-->
                  </td>
                  <td class="config" valign="top">
                    <input type="text" name="disallowin" value="<!--PHRASE::disallowin-->" id="disallowedin" class="text input_width">
                  </td>
                </tr>
                <tr>
                  <td class="config" valign="top">
                     <!--LANG::detail_callbacktype-->
                  </td>
                  <td class="config" valign="top">
                    <select name="callbacktype" class="input_width">
                      <option value="0"<!--IF::callbacktype?0--> selected<!--ENDIF-->>simple_replace</option>
                      <option value="1"<!--IF::callbacktype?1--> selected<!--ENDIF-->>simple_replace_single</option>
                      <option value="2"<!--IF::callbacktype?2--> selected<!--ENDIF-->>callback_replace</option>
                      <option value="3"<!--IF::callbacktype?3--> selected<!--ENDIF-->>callback_replace_single</option>
                      <option value="4"<!--IF::callbacktype?4--> selected<!--ENDIF-->>usecontent</option>
                      <option value="5"<!--IF::callbacktype?5--> selected<!--ENDIF-->>usecontent?</option>
                      <option value="6"<!--IF::callbacktype?6--> selected<!--ENDIF-->>callback_replace?</option>
                    </select>
                  </td>
                </tr>
                <tr id="icon">
                  <td class="config" valign="top">
                     <!--LANG::detail_icon--><br>
                     <span class="small"><!--LANG::detail_icon_text--><br>(optional)</span>
                  </td>
                  <td class="config" valign="top">
                    <input type="file" name="icon" class="text input_width">
                  </td>
                </tr>
                <tr>
                  <td class="config" valign="top">
                     <!--LANG::detail_active-->
                  </td>
                  <td class="config" valign="top">
                    <input type="radio" name="active" value="1"<!--IF::active?1--> checked<!--ENDIF-->> <!--LANG::detail_active_y--><br>
                    <input type="radio" name="active" value="0"<!--IF::active?0--> checked<!--ENDIF-->> <!--LANG::detail_active_n--><br>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" class="space"></td>
                </tr>
                <tr>
                  <td class="line" colspan="2">
                    <!--LANG::detail_flag-->
                  </td>
                </tr>
                <tr>
                  <td class="config" valign="top">
                    <!--LANG::detail_flag_name-->
                  </td>
                  <td class="config" valign="top">
                    <!--LANG::detail_flag_value-->
                  </td>
                </tr>
              </tbody>
              <tbody id="flags"><!--PHRASE::flags-->
                <tr>
                  <td class="config" valign="top">
                     <select name="flag[0][]" onchange="setflag(this.parentNode.parentNode.childNodes[3], this.value);">
                      <option value="0"></option>
                      <option value="1">case_sensitive</option>
                      <option value="2">closetag</option>
                      <option value="3">opentag.before.newline</option>
                      <option value="4">opentag.after.newline</option>
                      <option value="5">closetag.before.newline</option>
                      <option value="6">closetag.after.newline</option>
                      <option value="7">paragraph_type</option>
                      <option value="8">paragraphs</option>
                    </select>
                  </td>
                  <td class="config" valign="top">
                    <!--LANG::detail_flag_choose-->
                  </td>
                </tr>
              </tbody>
              <tbody>
                <tr>
                  <td class="config" colspan="2">
                    <input type="submit" name="addflag" value="Flag hinzuf&uuml;gen" class="text">
                  </td>
                </tr>
                <tr>
                  <td colspan="2" class="space"></td>
                </tr>
                <tr>
                  <td class="line" colspan="2">
                    <!--LANG::detail_replace-->
                  </td>
                </tr>
                <tr>
                  <td class="config" valign="top" colspan="2">
                    <!-- Editor-Bars with Buttons and Dropdowns -->

                    <div class="html-editor-bar" id="param_1_editor-bar">
                      <div class="html-editor-row-header">
                        <span id="param_1_title"><!--LANG::detail_replace_1_1--></span>
                        <span class="small">(<!--LANG::detail_replace_1_2-->)</span>
                      </div>
                      <div class="html-editor-row">
                        <div class="html-editor-button html-editor-button-active html-editor-button-line-numbers" onClick="toggelLineNumbers(this,'editor_param_1')" title="Zeilen-Nummerierung">
                            <img src="img/null.gif" alt="Zeilen-Nummerierung" border="0">
                        </div>
                        <!--PHRASE::taglist_1-->
                        <!--PHRASE::global_vars_1-->
                        <!--PHRASE::applets_1-->
                        <!--PHRASE::snippets_1-->
                      </div>
                    </div>

                    <!-- Editor and original Editor -->

                    <div id="param_1_content" style="background-color:#ffffff; border: 1px solid #999999; width:100%;">
                      <textarea class="no-js-html-editor"  rows="20" cols="66" name="param_1" id="param_1"><!--PHRASE::value_1--></textarea>
                    </div>
                    <script type="text/javascript">
                      editor_param_1 = new_editor ( "param_1", "325", false, 1 );
                    </script>

                    <!-- Footer and the rest -->

                    <div class="html-editor-path" id="param_1_footer">
                      <div style="padding:2px; height:13px;" class="small">
                        <span class="html-editor-highlighter">HTML</span>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr><td class="space"></td></tr>
                <tr>
                  <td class="config" valign="top" colspan="2">
                    <!-- Editor-Bars with Buttons and Dropdowns -->

                    <div class="html-editor-bar" id="param_2_editor-bar">
                      <div class="html-editor-row-header">
                        <span id="param_2_title"><!--LANG::detail_replace_2_1--></span>
                        <span class="small">(<!--LANG::detail_replace_2_2-->)</span>
                      </div>
                      <div class="html-editor-row">
                        <div class="html-editor-button html-editor-button-active html-editor-button-line-numbers" onClick="toggelLineNumbers(this,'editor_param_2')" title="Zeilen-Nummerierung">
                            <img src="img/null.gif" alt="Zeilen-Nummerierung" border="0">
                        </div>
                        <!--PHRASE::taglist_2-->
                        <!--PHRASE::global_vars_2-->
                        <!--PHRASE::applets_2-->
                        <!--PHRASE::snippets_2-->
                      </div>
                    </div>

                    <!-- Editor and original Editor -->

                    <div id="param_2_content" style="background-color:#ffffff; border: 1px solid #999999; width:100%;">
                      <textarea class="no-js-html-editor" rows="20" cols="66" name="param_2" id="param_2"><!--PHRASE::value_2--></textarea>
                    </div>
                    <script type="text/javascript">
                      editor_param_2 = new_editor ( "param_2", "325", false, 1 );
                    </script>

                    <!-- Footer and the rest -->

                    <div class="html-editor-path" id="param_2_footer">
                      <div style="padding:2px; height:13px;" class="small">
                        <span class="html-editor-highlighter">HTML</span>
                      </div>
                    </div>
                  </td>
                </tr>
                <!--IF::php?1-->
                <tr><td class="space"></td></tr>
                <tr>
                  <td class="config" valign="top" colspan="2">
                    <!-- Editor-Bars with Buttons and Dropdowns -->

                    <div class="html-editor-bar" id="php_editor-bar">
                      <div class="html-editor-row-header">
                        <span id="php_title"><!--LANG::detail_replace_3--></span>
                      </div>
                      <div class="html-editor-row">
                        <div class="html-editor-button html-editor-button-active html-editor-button-line-numbers" onClick="toggelLineNumbers(this,'editor_php')" title="Zeilen-Nummerierung">
                            <img src="img/null.gif" alt="Zeilen-Nummerierung" border="0">
                        </div>
                      </div>
                    </div>

                    <!-- Editor and original Editor -->

                    <div id="php_content" style="background-color:#ffffff; border: 1px solid #999999; width:100%;">
                      <textarea class="no-js-html-editor" rows="20" cols="66" name="php" id="php"><!--PHRASE::value_php--></textarea>
                    </div>
                    <script type="text/javascript">
                      editor_php = new_editor ( "php", "325", false, 4 );
                    </script>

                    <!-- Footer and the rest -->

                    <div class="html-editor-path" id="php_footer">
                      <div style="padding:2px; height:13px;" class="small">
                        <span class="html-editor-highlighter">PHP</span>
                      </div>
                    </div>
                  </td>
                </tr>
                <!--ENDIF-->
                <tr><td class="space"></td></tr>
                <tr>
                  <td colspan="2" class="buttontd">
                    <button class="button_new" type="submit" name="editcode">
                      <!--PHRASE::submitarrow-->
                      <!--PHRASE::submittext-->
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </form><!--ENDDEF-->

<!--DEF::flagselect-->                <tr>
                  <td class="config" valign="top">
                     <select name="flag[0][]" onchange="setflag(this.parentNode.parentNode.childNodes[3], this.value);">
                      <option value="0"></option>
                      <option value="1" <!--IF::flag?1-->selected<!--ENDIF-->>case_sensitive</option>
                      <option value="2" <!--IF::flag?2-->selected<!--ENDIF-->>closetag</option>
                      <option value="3" <!--IF::flag?3-->selected<!--ENDIF-->>opentag.before.newline</option>
                      <option value="4" <!--IF::flag?4-->selected<!--ENDIF-->>opentag.after.newline</option>
                      <option value="5" <!--IF::flag?5-->selected<!--ENDIF-->>closetag.before.newline</option>
                      <option value="6" <!--IF::flag?6-->selected<!--ENDIF-->>closetag.after.newline</option>
                      <option value="7" <!--IF::flag?7-->selected<!--ENDIF-->>paragraph_type</option>
                      <option value="8" <!--IF::flag?8-->selected<!--ENDIF-->>paragraphs</option>
                    </select>
                  </td>
                  <td class="config" valign="top">
                    <select name="flag[1][]" class="input_width">
<!--IF::flag?1--><option value="0"<!--IF::flagval?0--> selected<!--ENDIF-->>true</option>
<option value="1"<!--IF::flagval?1--> selected<!--ENDIF-->>false</option>
<!--ENDIF-->
<!--IF::flag?2--><option value="0"<!--IF::flagval?0--> selected<!--ENDIF-->>BBCODE_CLOSETAG_FORBIDDEN</option>
<option value="1"<!--IF::flagval?1--> selected<!--ENDIF-->>BBCODE_CLOSETAG_OPTIONAL</option>
<option value="2"<!--IF::flagval?2--> selected<!--ENDIF-->>BBCODE_CLOSETAG_IMPLICIT</option>
<option value="3"<!--IF::flagval?3--> selected<!--ENDIF-->>BBCODE_CLOSETAG_IMPLICIT_ON_CLOSE_ONLY</option>
<option value="4"<!--IF::flagval?4--> selected<!--ENDIF-->>BBCODE_CLOSETAG_MUSTEXIST</option><!--ENDIF-->
<!--IF::flag?3--><option value="0"<!--IF::flagval?0--> selected<!--ENDIF-->>BBCODE_NEWLINE_PARSE</option>
<option value="1"<!--IF::flagval?1--> selected<!--ENDIF-->>BBCODE_NEWLINE_IGNORE</option>
<option value="2"<!--IF::flagval?2--> selected<!--ENDIF-->>BBCODE_NEWLINE_DROP</option><!--ENDIF-->
<!--IF::flag?4--><option value="0"<!--IF::flagval?0--> selected<!--ENDIF-->>BBCODE_NEWLINE_PARSE</option>
<option value="1"<!--IF::flagval?1--> selected<!--ENDIF-->>BBCODE_NEWLINE_IGNORE</option>
<option value="2"<!--IF::flagval?2--> selected<!--ENDIF-->>BBCODE_NEWLINE_DROP</option><!--ENDIF-->
<!--IF::flag?5--><option value="0"<!--IF::flagval?0--> selected<!--ENDIF-->>BBCODE_NEWLINE_PARSE</option>
<option value="1"<!--IF::flagval?1--> selected<!--ENDIF-->>BBCODE_NEWLINE_IGNORE</option>
<option value="2"<!--IF::flagval?2--> selected<!--ENDIF-->>BBCODE_NEWLINE_DROP</option><!--ENDIF-->
<!--IF::flag?6--><option value="0"<!--IF::flagval?0--> selected<!--ENDIF-->>BBCODE_NEWLINE_PARSE</option>
<option value="1"<!--IF::flagval?1--> selected<!--ENDIF-->>BBCODE_NEWLINE_IGNORE</option>
<option value="2"<!--IF::flagval?2--> selected<!--ENDIF-->>BBCODE_NEWLINE_DROP</option><!--ENDIF-->
<!--IF::flag?7--><option value="1"<!--IF::flagval?1--> selected<!--ENDIF-->>BBCODE_PARAGRAPH_ALLOW_INSIDE</option>
<option value="2"<!--IF::flagval?2--> selected<!--ENDIF-->>BBCODE_PARAGRAPH_BLOCK_ELEMENT</option><!--ENDIF-->
<!--IF::flag?8--><option value="0"<!--IF::flagval?0--> selected<!--ENDIF-->>true</option>
<option value="1"<!--IF::flagval?1--> selected<!--ENDIF-->>false</option><!--ENDIF-->
                    </select>
                  </td>
                </tr><!--ENDDEF-->