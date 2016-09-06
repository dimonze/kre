<div class="separator"></div>
<h3>В настоящее время открыты следующие вакансии:</h3>

<?php foreach ($vacancies as $item): ?>
  <div class="vacancy_box">
	<h3 class="vacancy-name"><span>—</span> <b><?= $item->name ?></b></h3>
    <div class="vinfo">
      <?= $item->getRaw('description') ?>

      <h3>Отправить резюме</h3>
      <?php include_partial('vacancy/form', array('vacancy_id' => $item->id)) ?>
    </div>
  </div>
<?php endforeach ?>
