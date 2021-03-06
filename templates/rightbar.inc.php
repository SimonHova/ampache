<?php
/* vim:set softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * LICENSE: GNU General Public License, version 2 (GPLv2)
 * Copyright 2001 - 2013 Ampache.org
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License v2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */
?>
<ul id="rb_action">
    <li>
    <?php echo Ajax::button('?page=stream&action=basket','all', T_('Play'),'rightbar_play'); ?>
    </li>
    <li id="pl_add">
        <?php echo UI::get_icon('playlist_add', T_('Add to Playlist')); ?>
        <ul id="pl_action_additems" class="submenu">
          <li>
            <?php echo Ajax::text('?page=playlist&action=create', T_('Add to New Playlist'),'rb_create_playlist'); ?>
          </li>
        <?php
            $playlists = Playlist::get_users($GLOBALS['user']->id);
            Playlist::build_cache($playlists);
            foreach ($playlists as $playlist_id) {
                $playlist = new Playlist($playlist_id);
                $playlist->format();
        ?>
          <li>
            <?php echo Ajax::text('?page=playlist&action=append&playlist_id=' .  $playlist->id,$playlist->f_name,'rb_append_playlist_' . $playlist->id); ?>
          </li>
        <?php } ?>
        </ul>
    </li>
<?php if (Access::check_function('batch_download')) { ?>
    <li>
    <a href="<?php echo AmpConfig::get('web_path'); ?>/batch.php?action=tmp_playlist&amp;id=<?php echo $GLOBALS['user']->playlist->id; ?>">
            <?php echo UI::get_icon('batch_download', T_('Batch Download')); ?>
        </a>
    </li>
<?php } ?>
    <li>
    <?php echo Ajax::button('?action=basket&type=clear_all','delete', T_('Clear Playlist'),'rb_clear_playlist'); ?>
    </li>
    <li id="rb_add">
      <?php echo UI::get_icon('add', T_('Add Dynamic Items')); ?>
      <ul id="rb_action_additems" class="submenu">
       <li>
        <?php echo Ajax::text('?action=basket&type=dynamic&random_type=default', T_('Pure Random'),'rb_add_pure_random'); ?>
       </li>
       <li>
        <?php echo Ajax::text('?action=basket&type=dynamic&random_type=artist', T_('Related Artist'),'rb_add_related_artist'); ?>
       </li>
       <li>
        <?php echo Ajax::text('?action=basket&type=dynamic&random_type=album', T_('Related Album'),'rb_add_related_album'); ?>
       </li>
       <li>
        <?php echo Ajax::text('?action=basket&type=dynamic&random_type=tag', T_('Related Tag'),'rb_add_related_tag'); ?>
       </li>
      </ul>
    </li>
</ul>
<?php if (AmpConfig::get('play_type') == 'localplay') { require_once AmpConfig::get('prefix') . '/templates/show_localplay_control.inc.php'; } ?>
<ul id="rb_current_playlist">
<?php

    $objects = array();

    //FIXME :: this is kludgy
    if (!defined('NO_SONGS')) {
        $objects = $GLOBALS['user']->playlist->get_items();
    }

    // Limit the number of objects we show here
    if (count($objects) > 100) {
        $truncated = (count($objects) - 100);
        $objects = array_slice($objects, 0, 100, true);
    }

    $normal_array = array('radio','song','video','random','song_preview');

    foreach ($objects as $uid=>$object_data) {
        $type = array_shift($object_data);
        if (in_array($type,$normal_array)) {
            $object = new $type(array_shift($object_data));
            $object->format();
        }
        if ($type == 'random') {
            $object->f_link = Random::get_type_name($type);
        }
?>
<li class="<?php echo UI::flip_class(); ?>" >
  <?php echo $object->f_link; ?>
    <?php echo Ajax::button('?action=current_playlist&type=delete&id=' . $uid,'delete', T_('Delete'),'rightbar_delete_' . $uid,'','delitem'); ?>
</li>
<?php } if (!count($objects)) { ?>
    <li><span class="nodata"><?php echo T_('No items'); ?></span></li>
<?php } ?>
<?php if (isset($truncated)) { ?>
    <li class="<?php echo UI::flip_class(); ?>">
        <?php echo $truncated . ' ' . T_('More'); ?>...
    </li>
<?php } ?>
</ul>


<?php

// We do a little magic here to force a iframe reload depending on preference
// We do this last because we want it to load, and we want to know if there is anything
// to even pass
if (count($objects)) {
    Stream::run_playlist_method();
}
?>
