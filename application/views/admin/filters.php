<?php
include("header.php");
?>
	<script type="text/javascript" src="/js/jquery.liTranslit.js"></script>
	<script type="text/javascript" src="/js/admin/filters/filter.js"></script>
	<script type="text/javascript" src="/js/admin/filters/controller.js"></script>

	<table width="100%" height="100%" cellpadding="0" cellspacing="0">
	<tr>
	<td width="200px" valign="top"><?php include("menu.php"); ?></td>
	<td width="20px"></td>
	<td valign="top">
	<div class="title_border">
		<div class="content_title"><h1><?= $title ?></h1></div>
		<div class="back_and_exit">
					<span class="back_to_site"><a href="/" target="_blank" title="Открыть сайт в новом окне">Вернуться
							на сайт ></a></span>
			<span class="exit"><a href="/admin/login/logoff/">Выйти</a></span>
		</div>
	</div>
	<?php
	$opt_mlang = getOption('multilanguage');
	if (!$opt_mlang) {
		?>
		<div id="language" style="margin:20px auto;text-align: center; width: 150px; height: 25px;">
			<?php
			for ($i = 0; $i < count($langs); $i++) {
				$langs[$i] = trim($langs[$i]);
				if ($current_lang !== $langs[$i]) {
					?>
					<span class="langs"><?= $langs[$i] ?></span>&nbsp;
				<?php
				} else {
					?>
					<span class="curr_lang"><?= $langs[$i] ?></span>&nbsp;
				<?php
				}
			}
			?>
		</div>
	<?php
	} ?>
	<div class="content">
	<div class="top_menu">
		<!--div class = "top_menu_link"><a href = "/admin/filters/">Фильтры</a></div>
		<div class = "top_menu_link"><a href = "/admin/filters/add/">Добавить фильтр</a></div>
		<div class = "top_menu_link">
			<form action = "<?= $_SERVER['REQUEST_URI'] ?>" method = "post">
				Поиск:<input type = "text" name = "search" value = "<?php if (isset($_POST['search']))
			echo $_POST['search']; ?>" style = "width:500px"/>
				<input type = "submit" value = "Искать"/>
			</form>
		</div-->
	</div>

	<div class="filters">
	<?php
	if ($filters) {
		$count = count($filters);
		for ($f = 0; $f < $count; $f++) {
			$filter = $filters[$f];
			?>

			<div id="filter_<?= $filter['id'] ?>" class="filter">
			<form id="formFilter_<?= $filter['id'] ?>">
				<div class="filter_title">
					<input type="hidden" name="id" value="<?= $filter['id'] ?>"/>
					<input type="hidden" name="type" value="filter"/>
					<span class="filter_id">[<?= $filter['id'] ?>]</span>
					<?php
					if ($opt_mlang && $filter['multilanguage'] == 1) {
						?>
						<input type="hidden" name="curr_lang" value="<?= $current_lang ?>"/>
						<input type="text" name="title" class="inp_title editable"
							   value="<?= stripslashes(getLangText($filter['title'], $current_lang)) ?>" disabled data-backup=""
							   placeholder="Название" data-lang="<?= $current_lang ?>" required/>

					<?php
					} else {
						?>
						<input type="text" name="title" class="inp_title editable"
							   value="<?= stripslashes(getLangText($filter['title'])) ?>" disabled data-backup=""
							   placeholder="Название" required/>
					<?php
					}?>
					&nbsp;
					<input type="text" name="num" class="inp_num editable"
						   value="<?= $filter['num'] ?>" disabled data-lang="" data-backup="" placeholder="№"
						   required/>
				</div>
				<div class="control_icons">
					<?php if ($filter['active'] == 1) { ?>
						<img src="/images/admin/24_24/active.png" alt=""
							 class="img_button"
							 title="Активный" data-action="deactivate"/>
					<?php
					} else {
						?>
						<img src="/images/admin/24_24/passive.png" alt=""
							 class="img_button"
							 title="Не активный" data-action="activate"/>
					<?php
					}
					?>
					<img src="/images/admin/24_24/enabled.png" alt=""
						 class="img_button"
						 title="Редактировать" data-action="enabled"/>
					<img src="/images/admin/24_24/delete.png" alt=""
						 class="img_button"
						 title="Удалить" data-action="delete"/>
				</div>
				<div class="edit_control_icons">
					<img src="/images/admin/24_24/ok.png" alt=""
						 class="img_button" title="Принять"
						 data-action="accept"/>
					<img src="/images/admin/24_24/undo.png" alt=""
						 class="img_button" title="Отменить"
						 data-action="undo"/>
				</div>
				<?php
				if ($opt_mlang && $filter['multilanguage'] == 1) {
					?>
					<div class="filter_lang">
						<?php if (count($langs) > 2) { ?>
							<select class="select_filter_lang">
								<?php for ($i = 0; $i < count($langs); $i++) { ?>
									<option value="<?= $langs[$i] ?>" <?= ($current_lang === $langs[$i]) ? 'selected' : '' ?></option>><?= $langs[$i] ?></option>
                                <?php }
								?>
							</select>
						<?php
						} else {
							for ($i = 0; $i < count($langs); $i++) {
								$langs[$i] = trim($langs[$i]);
								if ($current_lang !== $langs[$i]) {
									?>
									<span class="langs"><?= $langs[$i] ?></span>&nbsp;
								<?php
								} else {
									?>
									<span class="curr_lang"><?= $langs[$i] ?></span>&nbsp;
								<?php
								}
							}
						}
						?>
					</div>
				<?php } ?>
				<div style="clear: both"></div>
				<div class="filter_info">
					<input type="text" class="editable" name="name" value="<?= $filter['name'] ?>"
						   disabled placeholder="Алиас" data-backup="" required/>
					<br/>
					<select name="show_type" class="editable" name="show_type" disabled
							data-backup="">
						<option
							value="none"<?= ($filter['show_type'] == "none") ? ' selected' : '' ?> >
							Нет
						</option>
						<option
							value="select"<?= ($filter['show_type'] == "select") ? ' selected' : '' ?>>
							Выпадающий список
						</option>
						<option
							value="checkbox"<?= ($filter['show_type'] == "checkbox") ? ' selected' : '' ?>>
							Флаг
						</option>
						<option
							value="radio"<?= ($filter['show_type'] == "radio") ? ' selected' : '' ?>>
							Переключатель
						</option>
					</select>
					<br/>
					<input type="text" class="editable" name="classes"
						   value="<?= stripslashes($filter['classes']) ?>" disabled placeholder="Классы"
						   data-backup=""/>
					<br/>
					<?php
					/*
					if ($opt_mlang) {
						?>
						<input type = "checkbox" class = "editable" name = "multilanguage"
							   disabled <?= ($filter['multilanguage'] == 1) ? 'checked' : '' ?>
							   data-backup = ""/>Мультиязычность
						<br/>
					<?php
					} */
					?>
					<input type="checkbox" class="editable" name="multiselect"
						   disabled <?= ($filter['multiselect'] == 1) ? 'checked' : '' ?>
						   data-backup=""/>Множественный выбор
					<br/>

					<?php
					if ($opt_mlang && $filter['multilanguage'] == 1) {
						?>
						<textarea class="editable" name="comment" disabled
								  data-lang="<?= $current_lang ?>"
								  data-backup="">
							<?= getLangText($filter['comment'], $current_lang) ?>
						</textarea>
					<?php
					} else {
						?>
						<textarea class="editable" name="comment" disabled
								  data-backup="">
							<?= getLangText($filter['comment']) ?>
						</textarea>

					<?php
					}?>


				</div>
			</form>
			<div class="filter_values">
				<div class="val_list">
					<div id="val_" class="new_value">
						<form id="formValue_">
							<input type="hidden" name="id" value=""/>
							<input type="hidden" name="type" value="value"/>
							<span class="value_id"></span>
							<input type="text" class="inp_text_val editable" name="value"
								   value=""
								   data-backup="" disabled required/>
							<input type="text" class="inp_num editable" name="num"
								   value="" data-backup=""
								   disabled placeholder="№"/>

							<div class="control_icons">
								<img src="/images/admin/16_16/enabled.png" alt=""
									 class="img_button" title="Редактировать"
									 data-action="enabled"/>
								<img src="/images/admin/16_16/delete.png" alt=""
									 class="img_button" title="Удалить" data-action="delete"/>
							</div>
							<div class="edit_control_icons">
								<img src="/images/admin/16_16/ok.png" alt=""
									 class="img_button" title="Принять"
									 data-action="accept"/>
								<img src="/images/admin/16_16/undo.png" alt=""
									 class="img_button" title="Удалить" data-action="undo"/>
							</div>
						</form>
					</div>
					<?php
					if (count($filter['values'])) {
						for ($v = 0; $v < count($filter['values']); $v++) {
							$value = $filter['values'][$v];
							?>
							<div id="val_<?= $value['id'] ?>" class="value">
								<form id="formValue_<?= $value['id'] ?>">
									<input type="hidden" name="id" value="<?= $value['id'] ?>"/>
									<input type="hidden" name="type" value="value"/>
									<span class="value_id">[<?= $value['id'] ?>]</span>

									<input type="text" class="inp_text_val editable" name="value"
										   value="<?= stripslashes(getLangText($value['value'])) ?>"
										   data-backup="" disabled required/>

									<input type="text" class="inp_num editable" name="num"
										   value="<?= $value['num'] ?>" data-backup=""
										   disabled placeholder="№"/>

									<div class="control_icons">
										<img src="/images/admin/16_16/enabled.png" alt=""
											 class="img_button" title="Редактировать"
											 data-action="enabled"/>
										<img src="/images/admin/16_16/delete.png" alt=""
											 class="img_button" title="Удалить" data-action="delete"/>
									</div>
									<div class="edit_control_icons">
										<img src="/images/admin/16_16/ok.png" alt=""
											 class="img_button" title="Принять"
											 data-action="accept"/>
										<img src="/images/admin/16_16/undo.png" alt=""
											 class="img_button" title="Удалить" data-action="undo"/>
									</div>
								</form>
							</div>
						<?php
						}
					} else {
						?>
						<span class="no_values">Нет значений для фильтра</span>
					<?php
					}
					?>
				</div>
				<div class="add_value">
					<span>Добавить значение</span><br>

					<form id="formNewValToFilter_<?= $filter['id'] ?>">
						<div style="float: left; width: 60%;">
							<input type="hidden" name="filter_id" value="<?= $filter['id'] ?>"/>
							<input type="hidden" name="type" value="value"/>
							<?php
							if ($opt_mlang && $filter['multilanguage'] == 1) {
								?>
								<input type="text" name="value" class="inp_text_val_new add_new" value=""
									   placeholder="Название" data-lang="<?= $current_lang ?>" required/>
							<?php
							} else {
								?>
								<input type="text" name="value" class="inp_text_val_new add_new" value=""
									   placeholder="Название" required/>
							<?php
							}?>
						</div>
						&nbsp;
						<div style="float: right; width: 35%;">
							<input type="text" name="num" class="inp_num add_new" placeholder="№" size="10"/>
							&nbsp;
							<img src="/images/admin/16_16/ok.png" alt="" class="img_button"
								 title="Добавить значение" data-action="addValue"/>
							<img src="/images/admin/16_16/delete.png" alt="" class="img_button"
								 title="Очистить" data-action="clear"/>
						</div>
						<div style="clear: both;"></div>
					</form>
				</div>
			</div>
			</div>
		<?php
		}
	} else {
		?>
		<span class="no_filters">Нет фильтров</span>
	<?php
	}
	?>
	<div style="clear: both"></div>
	</div>
	<div class="add_filter">
		<?php
		if (!empty($_SESSION['filter_already_created'])) {
			echo $_SESSION['filter_already_created'];
			unset($_SESSION['filter_already_created']);
		}
		?>
		<div class="add_center">
			<form id="formNewFilter">
				<input type="hidden" name="type" value="filter"/>

				<div class="col_left">
					<div class="new_val_list">
						<div class="new_val">
							<input type="text" class="add_new" name="values[value][]"
								   placeholder="Новый параметр">
							&nbsp;
							<input type="text" class="add_new inp_num" name="values[num][]"
								   placeholder="№"/>
						</div>
						<div class="new_val">
							<input type="text" class="add_new" name="values[value][]"
								   placeholder="Новый параметр">
							&nbsp;
							<input type="text" class="add_new inp_num" name="values[num][]"
								   placeholder="№"/>
						</div>
						<div class="new_val">
							<input type="text" class="add_new" name="values[value][]"
								   placeholder="Новый параметр">
							&nbsp;
							<input type="text" class="add_new inp_num" name="values[num][]"
								   placeholder="№"/>
						</div>
						<a id="add_new_val" href="#add_new_val"
						   style="font-size: smaller;">Добавить параметр</a>
					</div>
				</div>
				<div class="col_left">
					<input type="text" name="title" class="add_new" placeholder="Имя фильтра*" required><br/>
					<input type="text" name="name" class="add_new" placeholder="Алиас*" maxlength="20" required><br/>
					<input type="text" name="num" class="add_new inp_num" placeholder="№" maxlength="20"><br/>
					<input type="text" name="classes" class="add_new" placeholder="Классы">
				</div>
				<div class="col_left">
					<select name="show_type" class="add_new">
						<option value="none" selected="">Нет</option>
						<option value="select">Выпадающий список</option>
						<option value="checkbox">Флаг</option>
						<option value="radio">Переключатель</option>
					</select>
					<br/>
					<input type="checkbox" name="multiselect" class="add_new">Множественный выбор
				</div>
				<div class="col_left">
					<textarea name="comment" class="add_new" placeholder="Комментарий" cols="50" rows="4"></textarea>
				</div>
				<div class="control_icons">
					<img src="/images/admin/24_24/ok.png" alt=""
						 class="img_button" title="Принять"
						 data-action="addFilter"/>
					<img src="/images/admin/24_24/undo.png" alt=""
						 class="img_button" title="Удалить" data-action="clear"/>
				</div>
			</form>
			<div style="clear: both"></div>
		</div>
	</div>


	</div>
	</td>
	</tr>
	</table>

<?php
include("footer.php");
?>