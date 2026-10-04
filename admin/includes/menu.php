<div id="dialog1" title="<?php echo Accueil ?>">
<p><?php  accueil() ?></p>
</div>
<li id="draggable" class="ui-widget-content ui-corner-tr" style="text-align:center;background:none;border:none;padding-left:20px !important; padding-right:10px !important;margin-left:35px;float:left;list-style-type:none;margin-top:25px;"><div style="margin-bottom:10px;"><div id="opener1"><img src="images/home.png"><h5 class="ui-widget-header" style="background:none;margin:0px;"><?php echo Accueil ?></h5></div></div></li>

<div id="dialog2" title="<?php echo Articles ?>">
<p><iframe src="<?php echo''.base64_decode($tableau[5]).''; ?>/admin/index2.php?page=liste" style="background:none;border:none;min-width:100%;min-height:270px !important;background-image:none;"></iframe>

<a href="<?php echo''.base64_decode($tableau[5]).''; ?>/admin/index2.php?page=liste" target="cwindow"></a>
</p>
</div>
<li id="draggable2" class="ui-widget-content ui-corner-tr" style="text-align:center;background:none;border:none;padding-left:20px !important; padding-right:10px !important;margin-left:35px;float:left;list-style-type:none;margin-top:25px;"><div style="margin-bottom:10px;"><div id="opener2"><img src="images/list.png"><h5 class="ui-widget-header" style="background:none;margin:0px;"><?php echo Articles ?></h5></div></div></li>

<div id="dialog3" title="<?php echo Ecrire ?>">
<iframe src="<?php echo''.base64_decode($tableau[5]).''; ?>/admin/index2.php?page=ajouter" style="border:none;min-width:100%;min-height:430px !important;background-image:none;"></iframe>

<a href="<?php echo''.base64_decode($tableau[5]).''; ?>/admin/index2.php?page=ajouter" target="cwindow"></a>

</div>
<li id="draggable3" class="ui-widget-content ui-corner-tr" style="text-align:center;background:none;border:none;padding-left:20px !important; padding-right:10px !important;margin-left:35px;float:left;list-style-type:none;margin-top:25px;"><div style="margin-bottom:10px;"><div id="opener3"><img src="images/ecrire.png"><h5 class="ui-widget-header" style="background:none;margin:0px;"><?php echo Ecrire ?></h5></div></div></li>

<div id="dialog4" title="<?php echo Images ?>">
<p><iframe src="<?php echo''.base64_decode($tableau[5]).''; ?>/admin/index2.php?page=images" style="border:none;min-width:100%;min-height:200px !important;background-image:none;"></iframe>

<a href="<?php echo''.base64_decode($tableau[5]).''; ?>/admin/index2.php?page=images" target="cwindow"></a>
</p>
</div>
<li id="draggable4" class="ui-widget-content ui-corner-tr" style="text-align:center;background:none;border:none;padding-left:20px !important; padding-right:10px !important;margin-left:35px;float:left;list-style-type:none;margin-top:25px;"><div style="margin-bottom:10px;"><div id="opener4"><img src="images/pictures.png"><h5 class="ui-widget-header" style="background:none;margin:0px;"><?php echo Images ?></h5></div></div></li>

<div id="dialog5" title="<?php echo Configuration ?>">
<p><iframe src="<?php echo''.base64_decode($tableau[5]).''; ?>/admin/index2.php?page=configuration" style="border:none;min-width:100%;min-height:500px !important;background-image:none;"></iframe>

<a href="<?php echo''.base64_decode($tableau[5]).''; ?>/admin/index2.php?page=configuration" target="cwindow"></a>
</p>
</div>
<li id="draggable5" class="ui-widget-content ui-corner-tr" style="text-align:center;background:none;border:none;padding-left:20px !important; padding-right:10px !important;margin-left:35px;float:left;list-style-type:none;margin-top:25px;"><div style="margin-bottom:10px;"><div id="opener5"><img src="images/configure.png"><h5 class="ui-widget-header" style="background:none;margin:0px;"><?php echo Configuration ?></h5></div></div></li>

<div id="dialog6" title="<?php echo Blog ?>">
<p><?php blog(); ?></p>
</div>

<li id="draggable6" class="ui-widget-content ui-corner-tr" style="text-align:center;background:none;border:none;padding-left:20px !important; padding-right:10px !important;margin-left:35px;float:left;list-style-type:none;margin-top:25px;"><div style="margin-bottom:10px;"><div id="opener6"><img src="images/eye.png"><h5 class="ui-widget-header" style="background:none;margin:0px;">Blog</div></div></li>

<li id="draggable8" class="ui-widget-content ui-corner-tr" style="text-align:center;background:none;border:none;padding-left:20px !important; padding-right:10px !important;margin-left:35px;float:left;list-style-type:none;margin-top:25px;"><div style="margin-bottom:10px;"><a href="mfa.php?mode=recovery"><h5 class="ui-widget-header" style="background:none;margin:0px;">Codes de secours</h5></a></div></li>

<li id="draggable9" class="ui-widget-content ui-corner-tr" style="text-align:center;background:none;border:none;padding-left:20px !important; padding-right:10px !important;margin-left:35px;float:left;list-style-type:none;margin-top:25px;"><div style="margin-bottom:10px;"><a href="comments.php"><h5 class="ui-widget-header" style="background:none;margin:0px;">Commentaires</h5></a></div></li>

<li id="draggable10" class="ui-widget-content ui-corner-tr" style="text-align:center;background:none;border:none;padding-left:20px !important; padding-right:10px !important;margin-left:35px;float:left;list-style-type:none;margin-top:25px;"><div style="margin-bottom:10px;"><a href="update.php"><h5 class="ui-widget-header" style="background:none;margin:0px;">Mise à jour</h5></a></div></li>

<li id="draggable7" class="ui-widget-content ui-corner-tr" style="text-align:center;background:none;border:none;padding-left:20px !important; padding-right:10px !important;margin-left:35px;float:left;list-style-type:none;margin-top:25px;"><div style="margin-bottom:10px;"><form action="deconnexion.php" method="post"><?php echo elodie_cms_csrf_input(); ?><button type="submit" title="<?php echo Deconnexion ?>"><img src="images/logout.png" alt=""><h5 class="ui-widget-header" style="background:none;margin:0px;"><?php echo Deconnexion ?></h5></button></form></div></li>

</ul>