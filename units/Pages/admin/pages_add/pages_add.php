<?php echo $this->blocks->put("locals", "locals_for_add", array("parent" => $this_block)); ?>

<div class='bookmarks_box'>
	<a href='javascript:void(0);' class='link_bookmarks' id='book_0' onclick='_book.book_change(this);'><?php echo $langs['books_main']; ?></a>
</div>

<div class='bookmarks_content book_box_0'>
	<table>
		<tr>
			<td>
				<?php echo $this->blocks->put('uploads', 'uploads_block', array("parent" => $this_block)); ?>
			</td>
				<td>
					<table>
						<tr>
							<td class="td_texts">
								<?php echo _input("pages_name", TRUE); ?>
							</td>
						</tr>
						<tr>
							<td class="td_texts">
								<?php echo $this->blocks->put("aliases", "aliases_add", array('target' => 'pages_name')); ?>
							</td>
						</tr>
						<tr>
							<td class="td_texts">
								<?php echo _area("pages_text", TRUE, TRUE); ?>
								<input id="pages_text_code" type="hidden" />
							</td>
						</tr>
						<tr>
							<td class="td_texts">
								<?php echo _checkbox("pages_active"); ?>
							</td>
						</tr>
						<tr>
							<td class="td_texts">
								<?php echo _checkbox("pages_mark"); ?>
							</td>
						</tr>
					</table>
				</td>
		</tr>
	</table>
</div>