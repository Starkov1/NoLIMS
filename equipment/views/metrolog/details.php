<?php
	use yii\helpers\Url;
	$this->registerJsFile('@web/assets/vendor/vue/vue.min.js'); 
	$this->registerJsFile('@web/assets/vendor/vue/axios.min.js');
	$this->registerJsFile('@web/assets/js/equipment/metrolog_equipment_details.js');
?>
<div class="row" id="details">
	<div class="sixteen wide column">
		<div class="ui form">
			<div class="ui fluid card">
				<div class="content">
					<a href="<?php echo Url::toRoute(['equipments/']) ?>" class="ui right floated orange button">Отмена</a>
					<a class="ui right floated green button" v-on:click="Submit()">Сохранить</a>
				</div>
			</div>
			<div class="ui fluid card">
				<div class="content">
					<div class="header">Идентификационные данные</div>
				</div>
				<div class="content">
					<div class="ui form">
						<div class="two fields">
							<div class="field">
								<label>Наименование</label>
								<textarea cols="30" rows="2" v-model="listDetails.equipment.title"></textarea>
							</div>
							<div class="field">
								<label>Производитель</label>
								<textarea cols="30" rows="2" v-model="listDetails.equipment.manufacturer"></textarea>
							</div>
						</div>
						<div class="four fields">
							<div class="field">
								<label>Инвентарный номер</label>
								<input type="text" v-model="listDetails.equipment.inventory_number">
							</div>
							<div class="field">
								<label>Серийный номер</label>
								<input type="text" v-model="listDetails.equipment.serial_number">
							</div>
							<div class="field">
								<label>Модель</label>
								<input type="text" v-model="listDetails.equipment.model">
							</div>
							<div class="field">
								<label>Дата изготовления</label>
								<input type="date" v-model="listDetails.equipment.date_create">
							</div>
						</div>
						<div class="two fields">
							<div class="field">
								<label>Номер</label>
								<input type="text" v-model="listDetails.equipment.number">
							</div>
							<div class="field">
								<label>ФИФ</label>
								<input type="text" v-model="listDetails.equipment.fif_number">
							</div>
						</div>
						<div class="field">
							<label>Дата ввода в эксплуатацию</label>
							<input type="date" v-model="listDetails.equipment.date_commissioning">
						</div>
						<div class="field">
							<label>Примечание</label>
							<textarea cols="30" rows="2" v-model="listDetails.equipment.description"></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="ui fluid card">
				<div class="content">
					<div class="header">Состояние</div>
				</div>
				<div class="content">
					<div class="ui form">
						<div class="inline fields">
							<div class="field">
								<div class="ui checkbox">
									<input type="checkbox" v-model="listDetails.equipment.is_archive">
									<label>Архив</label>
								</div>
							</div>
							<div class="field">
								<div class="ui checkbox">
									<input type="checkbox" v-model="listDetails.equipment.is_working">
									<label>Используется</label>
								</div>
							</div>
							<div class="field">
								<div class="ui checkbox">
									<input type="checkbox" v-model="listDetails.equipment.is_conservation">
									<label>Консервация</label>
								</div>
							</div>
							<div class="field">
								<div class="ui checkbox">
									<input type="checkbox" v-model="listDetails.equipment.is_repair">
									<label>Ремонт</label>
								</div>
							</div>
							<div class="field">
								<div class="ui checkbox">
									<input type="checkbox" v-model="listDetails.equipment.is_check">
									<label>ЦСМ</label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ui fluid card">
				<div class="content">
					<div class="header">Местоположение</div>
				</div>
				<div class="content">
					<div class="ui form">
						<div class="two fields">
							<div class="field">
								<label>Отдел</label>
								<select class="ui search dropdown disabled" v-model="listDetails.equipment.id_department">
									<option v-for="department in listDepartment" v-bind:value="department.id_department">{{ department.department }}</option>
								</select>
							</div>
							<div class="field">
								<label>Кабинет</label>
								<select class="ui search dropdown" v-model="listDetails.equipment.id_location">
									<option v-for="location in filteredLocation" v-bind:value="location.id">{{ location.cabinet_number }}</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ui fluid card">
				<div class="content">
					<div class="header">История проверок</div>
				</div>
				<div class="content">
					<table class="ui compact table" v-if="listDetails.history_check || listDetails.current_check">
						<thead>
							<tr>
								<th>Текущая</th>
								<th>Следующая</th>
								<th>Вид документа</th>
								<th>№ документа</th>
								<th>Документ</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="check in listDetails.history_check">
								<td>{{ today(check.date_current_check) }}</td>
								<td>{{ today(check.date_next_check) }}</td>
								<td>{{ check.document_type }}</td>
								<td>{{ check.number_document }}</td>
								<td>
									<a v-if="check.upload_file_name" v-bind:href="'/assets/uploads/' + check.upload_file_name" target="_blank">Открыть</a>
									<span v-if="!check.upload_file_name">Не загружен</span>
								</td>
								<td class="collapsing">
									<button class="ui blue mini icon button" v-on:click="showModal('CheckEdit', check)"><i class="icon edit"></i></button>
								</td>
							</tr>
							<tr>
								<td>{{ today(listDetails.current_check.date_current_check) }}</td>
								<td>{{ today(listDetails.current_check.date_next_check) }}</td>
								<td>{{ listDetails.current_check.document_type }}</td>
								<td>{{ listDetails.current_check.number_document }}</td>
								<td>
									<a v-if="listDetails.current_check.upload_file_name" v-bind:href="'/assets/uploads/' + listDetails.current_check.upload_file_name" target="_blank">Открыть</a>
									<span v-if="!listDetails.current_check.upload_file_name">Не загружен</span>
								</td>
								<td class="collapsing">
									<button class="ui blue mini icon button" v-on:click="showModal('CheckEdit', listDetails.current_check)"><i class="icon edit"></i></button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="ui fluid card">
				<div class="content">
					<div class="header">Перемещения <span class="right floated"><button class="ui green right floated mini icon button" v-on:click="showModal('Handoff')"><i class="icon exchange"></i></button></span></div>						
				</div>
				<div class="content">
					<table class="ui compact table" v-if="listDetails.history_moving">
						<thead>
							<tr>
								<th>Прошлый отдел</th>
								<th>Текущий отдел</th>
								<th>Прошлый кабинет</th>
								<th>Текущий кабинет</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="moving in listDetails.history_moving">
								<td>{{ moving.current_department }}</td>
								<td>{{ moving.next_department }}</td>
								<td>{{ moving.current_location }}</td>
								<td>{{ moving.next_location }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="ui fluid card">
				<div class="content">
					<div class="header">Характеристки</div>
				</div>
				<div class="content">
					<div class="ui form">
						<div class="field">
							<label>Вид</label>
							<select class="ui search dropdown" v-model="listDetails.equipment.id_equipment_type">
								<option v-for="type in listDetails.types.type" v-bind:value="type.id">{{ type.title }}</option>
							</select>
						</div>
						<div class="two fields">
							<div class="field">
								<label>Диапазон измерений</label>
								<input type="text" v-model="listDetails.equipment.measuring_range">
							</div>
							<div class="field">
								<label>Диапазон работы</label>
								<input type="text" v-model="listDetails.equipment.measuring_work">
							</div>
						</div>
						<div class="two fields">
							<div class="field">
								<label>Точность <span>(<span v-on:click="addPlus()">±</span> / <span v-on:click="addTemp()">°</span>)</span></label>
								<input type="text" v-model="listDetails.equipment.accuracy">
							</div>
							<div class="field">
								<label>Класс точности</label>
								<input type="text" v-model="listDetails.equipment.class_accuracy">
							</div>
						</div>
						<div class="three fields">
							<div class="field">
								<label>Цель использования</label>
								<input type="text" v-model="listDetails.equipment.purpose_of_use">
							</div>
							<div class="field">
								<label>Объект исследования</label>
								<select class="ui search dropdown" v-model="listDetails.equipment.id_object_study">
									<option v-for="object in listObjectStudy" v-bind:value="object.id">{{ object.title }}</option>
								</select>
							</div>
							<div class="field">
								<label>Функциональное значение</label>
								<select class="ui search dropdown" v-model="listDetails.equipment.id_function_of_use">
									<option v-for="location in filteredFunctionOfUse" v-bind:value="location.id">{{ location.title }}</option>
								</select>
							</div>
						</div>
						<div class="field">
							<label>Дополнительные характеристики</label>
							<textarea cols="30" rows="2" v-model="listDetails.equipment.characteristics"></textarea>
						</div>
						<div class="two fields">
							<div class="field">
								<div class="ui green label">Инструкция по эксплуатации</div>
							</div>
							<div class="field">
								<a class="ui right floated red mini button" v-on:click="showModal('EditInstruction')">Изменить</a>
								<a v-if="listDetails.instruction" v-bind:href="'/assets/uploads/' + listDetails.instruction.upload_file" class="ui right floated yellow mini button" target="_blank">Открыть</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ui fluid card">
				<div class="content">
					<div class="header">Условия работы</div>
				</div>
				<div class="content">
					<div class="ui form">
						<div class="three fields">
							<div class="field">
								<label>Влажность</label>
								<input type="text" v-model="listDetails.condition_working.humidity">
							</div>
							<div class="field">
								<label>Давление</label>
								<input type="text" v-model="listDetails.condition_working.pressure">
							</div>
							<div class="field">
								<label>Температура</label>
								<input type="text" v-model="listDetails.condition_working.temperature">
							</div>
						</div>
						<div class="two fields">
							<div class="field">
								<label>Напряжение</label>
								<input type="number" v-model="listDetails.condition_working.voltage">
							</div>
							<div class="field">
								<label>Ток</label>
								<input type="number" v-model="listDetails.condition_working.amperage">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ui fluid card">
				<div class="content">
					<div class="header">Требуемое техническое обслуживание <span class="right floated"><button class="ui yellow right floated mini icon button" v-on:click="showModal('Maintenance')"><i class="icon plus"></i></button></span></div>
				</div>
				<div class="content">
					<table class="ui compact table" v-if="listDetails.maintenance">
						<thead>
							<tr>
								<th>Вид ТО</th>
								<th>Исполнитель</th>
								<th>Периодичность</th>
								<th>ТО</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="maintenance in listDetails.maintenance">
								<td>{{ maintenance.type_maintenance }}</td>
								<td>{{ maintenance.executor }}</td>
								<td>{{ maintenance.periodicity }}</td>
								<td>{{ maintenance.description }}</td>
								<td class="collapsingl">
									<button class="ui blue mini icon button" v-on:click="showModal('MaintenanceEdit', maintenance)"><i class="icon edit"></i></button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div id="modalHandoff" class="ui tiny card modal">
		<div class="content">
			<div class="header">Перемещение</div>
			<!-- <div class="meta">{{ handoff.department }}</div> -->
		</div>
		<div class="content">
			<div class="ui form">
				<div class="two fields">
					<div class="field">
						<label>Переместить в отдел</label>
						<select class="ui search dropdown" v-model="listDetails.equipment.id_department">
							<option v-for="department in listDepartment" v-bind:value="department.id_department">{{ department.department }}</option>
						</select>
					</div>
					<div class="field">
						<label>Кабинет</label>
						<select class="ui search dropdown" v-model="listDetails.equipment.id_location">
							<option v-for="location in filteredLocation" v-bind:value="location.id">{{ location.cabinet_number }}</option>
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="actions">
			<button class="ui approve green button" v-on:click="setHandoff()">Сохранить</button>
			<button class="ui deny orange button">Отмена</button>
		</div>
	</div>
	<div id="modalMaintenance" class="ui small card modal">
		<div class="content">
			<div class="header">Техническое обслуживание</div>
			<!-- <div class="meta">{{ handoff.department }}</div> -->
		</div>
		<div class="content">
			<div class="ui form">
				<div class="two fields">
					<div class="field">
						<label>Вид ТО</label>
						<select class="ui search dropdown" v-model="maintenance.id_type_maintenance">
							<option v-for="maintenance in listMaintenance.type_maintenance" v-bind:value="maintenance.id">{{ maintenance.title }}</option>
						</select>
					</div>
					<div class="field">
						<label>Исполнитель</label>
						<select class="ui search dropdown" v-model="maintenance.id_executor">
							<option v-for="executor in listMaintenance.executor" v-bind:value="executor.id">{{ executor.title }}</option>
						</select>
					</div>
				</div>
				<div class="two fields">
					<div class="field">
						<label>Периодичность</label>
						<input type="text" v-model="maintenance.periodicity">
					</div>
					<div class="field">
						<label>Тех. обслуживание</label>
						<select class="ui search dropdown" v-model="maintenance.id_maintenance">
							<option v-for="maintenance in listMaintenance.list_maintenance" v-bind:value="maintenance.id">{{ maintenance.description }}</option>
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="actions">
			<button class="ui approve green button" v-on:click="appendMaintenance()">Добавить</button>
			<button class="ui deny orange button">Отмена</button>
		</div>
	</div>
	<div id="modalCheckEdit" class="ui tiny card modal">
		<div class="content">
			<div class="header">Изменения проверки</div>
		</div>
		<div class="content" v-if="dateCheck">
			<div class="ui form">
				<div class="two fields">
					<div class="field">
						<label>Текущая (пройденная)</label>
						<input type="date" v-model="dateCheck.date_current_check">
					</div>
					<div class="field">
						<label>Cледующая (предстоящая)</label>
						<input type="date" v-model="dateCheck.date_next_check">
					</div>
				</div>
				<div class="field">
					<label>Загружамый файл</label>
					<select class="ui search dropdown" v-model="dateCheck.id_upload_document_type">
						<option v-for="doc in listDocType" v-bind:value="doc.id">{{ doc.title }}</option>
					</select>
				</div>
				<div class="field">
					<label>Номер документа</label>
					<input type="text" v-model="dateCheck.number_document">
				</div>
				<div class="field">
					<input type="file" ref="upload_file_name" v-on:change="handleFileUpload()">
				</div>
			</div>
		</div>
		<div class="actions">
			<button class="ui approve green button" v-on:click="saveCheck()">Сохранить</button>
			<button class="ui deny orange button">Отмена</button>
		</div>
	</div>
	<div id="modalMaintenanceEdit" class="ui tiny card modal">
		<div class="content">
			<div class="header">Изменения ТО</div>
		</div>
		<div class="content" v-if="maintenanceEdit">
			<div class="ui form">
				<div class="field">
					<label>Вид ТО</label>
					<!-- <input type="text" v-model="maintenanceEdit.id_type_maintenance"> -->
					<select class="ui search dropdown" v-model="maintenanceEdit.id_type_maintenance">
						<option v-for="maintenance in listMaintenance.type_maintenance" v-bind:value="maintenance.id">{{ maintenance.title }}</option>
					</select>
				</div>
				<div class="field">
					<label>Исполнитель</label>
					<!-- <input type="text" v-model="maintenanceEdit.id_executor"> -->
					<select class="ui search dropdown" v-model="maintenanceEdit.id_executor">
						<option v-for="executor in listMaintenance.executor" v-bind:value="executor.id">{{ executor.title }}</option>
					</select>
				</div>
				<div class="field">
					<label>Периодичность</label>
					<input type="text" v-model="maintenanceEdit.periodicity">
				</div>
				<div class="field">
					<label>Тех. обслуживание</label>
					<!-- <input type="text" v-model="maintenanceEdit.description"> -->
					<select class="ui search dropdown" v-model="maintenanceEdit.id_maintenance">
						<option v-for="maintenance in listMaintenance.list_maintenance" v-bind:value="maintenance.id">{{ maintenance.description }}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="actions">
			<button class="ui approve green button" v-on:click="saveMaintenances()">Сохранить</button>
			<button class="ui deny orange button">Отмена</button>
		</div>
	</div>
	<div id="modalEditInstruction" class="ui tiny card modal">
		<div class="content">
			<div class="header">Изменение инструкции</div>
		</div>
		<div class="content">
			<div class="ui form">
				<div class="field">
					<label>Номер инструкции</label>
					<select class="ui search dropdown" v-model="id_instruction">
						<option v-for="instruction in listInstructions" v-bind:value="instruction.id">{{ instruction.id }}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="actions">
			<button class="ui approve green button" v-on:click="saveInstructions()">Сохранить</button>
			<button class="ui deny orange button">Отмена</button>
		</div>
	</div>
</div>