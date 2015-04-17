<div id="wrapper_glossary_content">
    <div id="glossarylist">
        <div style="height:370px;">
                    <table width="100%" align="left">
                    <tbody><tr><td valign="top" align="left">
                    <div align="center" class="quiz_content_actions" style="margin:0px;width:80%;">A - Z</div>
                    </td></tr>
                    <tr><td>
                     <?php
                     if (count($glossaryList) > 0) {
                         $addPageToUrl = "";
                         if (isset($_GET['page'])) {
                            $addPageToUrl = '&page='.Security::remove_XSS($_GET['page']); 
                         }
                         foreach ($glossaryList as $glossaryinfo) {
                             echo '<a href="'.  api_get_self().'?'.  api_get_cidreq().'&amp;id='.$glossaryinfo['id'].'&amp;action=showterm'.$addPageToUrl.'">'.Display::return_icon('pixel.gif',$glossaryinfo['name'], array('class' => 'actionplaceholdericon actionpreview')).' '.$glossaryinfo['name'].'</a><br/><br/>';
                         }
                     } else {
                         echo get_lang('ThereAreNoDefinitionsHere');
                     }

                     ?>
                    </td></tr>
                    </tbody>
                    </table>
                    <div class="pager" align="center"><?php echo $pagerLinks; ?></div>    
        </div>
    </div>
    <div id="whiteboard">
        <table width="100%">
            <tbody><tr>
            <td width="70%" valign="top" align="center" style="background: url(../.././../img/whiteboard.png) no-repeat 100px 5px;">
            <table width="50%" cellspacing="5" cellpadding="5">
            <tbody><tr>
            <td>&nbsp;</td>
            </tr><tr>
                <td class="glossary_whiteboard_text"><a id="glossary_a" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=a'; ?>">A</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_b" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=b'; ?>">B</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_c" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=c'; ?>">C</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_d" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=d'; ?>">D</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_e" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=e'; ?>">E</a></td>
            </tr><tr>
                <td class="glossary_whiteboard_text"><a id="glossary_f" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=f'; ?>">F</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_g" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=g'; ?>">G</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_h" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=h'; ?>">H</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_i" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=i'; ?>">I</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_j" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=j'; ?>">J</a></td>
            </tr><tr>
                <td class="glossary_whiteboard_text"><a id="glossary_k" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=k'; ?>">K</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_l" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=l'; ?>">L</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_m" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=m'; ?>">M</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_n" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=n'; ?>">N</a></td>
            </tr><tr>
                <td class="glossary_whiteboard_text"><a id="glossary_o" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=o'; ?>">O</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_p" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=p'; ?>">P</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_q" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=q'; ?>">Q</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_r" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=r'; ?>">R</a></td>
            </tr><tr>
                <td class="glossary_whiteboard_text"><a id="glossary_s" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=s'; ?>">S</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_t" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=t'; ?>">T</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_u" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=u'; ?>">U</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_v" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=v'; ?>">V</a></td>
            </tr><tr>
                <td class="glossary_whiteboard_text"><a id="glossary_w" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=w'; ?>">W</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_x" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=x'; ?>">X</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_y" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=y'; ?>">Y</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_z" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=z'; ?>">Z</a></td>
                <td class="glossary_whiteboard_text"><a id="glossary_az" href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;action=listterm&word=az'; ?>">A - Z</a></td>
            </tr><tr>
            <td>&nbsp;</td>
            </tr>
            </tbody></table>
            </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div id="glossary_image">
            <img  src="../.././../img/instructor_analysis.png" class="abs">
    </div>
</div>