<?php
namespace app\modules\equipment\controllers;
use Yii;
use yii\web\Controller;
use app\modules\equipment\models\location;
use app\modules\equipment\models\department;
use app\modules\equipment\models\view_metrolog_equipment;
use app\modules\equipment\models\view_equipment_metrolog_sticker;
use app\modules\equipment\models\view_equipment_metrolog_card;
use app\modules\equipment\models\view_equipment_metrolog_list_work_for_equipment;
use app\modules\equipment\models\equipment_type;
use app\modules\equipment\models\equipment_equipment;
use app\modules\equipment\models\equipment_condition_working;
use app\modules\equipment\models\equipment_upload_document_type;
use app\modules\equipment\models\equipment_equipment_details;
use app\modules\equipment\models\equipment_function_of_use;
use app\modules\equipment\models\equipment_details_date_check;
use app\modules\equipment\models\equipment_date_check;
use app\modules\equipment\models\equipment_details_history_date_check;
use app\modules\equipment\models\equipment_moving_history;
use app\modules\equipment\models\equipment_object_study;
use app\modules\equipment\models\equipment_executor;
use app\modules\equipment\models\equipment_list_maintenance;
use app\modules\equipment\models\equipment_type_maintenance;
use app\modules\equipment\models\equipment_list_work_maintenance;
use app\modules\equipment\models\equipment_kit_equipment;
use app\modules\equipment\models\view_equipment_kits;
use app\modules\equipment\models\equipment_checks;
use app\modules\equipment\models\view_equipment_check;
use app\modules\equipment\models\equipment_total_check;
use app\modules\equipment\models\equipment_list_maintenances;
use app\modules\equipment\models\equipment_list_works_plan;
use app\modules\equipment\models\UploadForm;
use yii\web\UploadedFile;
require 'D:/OSPanel/vendor/autoload.php';
use PHPJasper\PHPJasper;

class MetrologController extends Controller
{
	public $layout = 'main_metrolog';

	public function beforeAction($action)
	{
		if ($action->id == 'append-equipment' || $action->id == 'upload-file' || $action->id == 'change-check'
			|| $action->id == 'create-sticker' || $action->id == 'set-tag' || $action->id == 'set-handoff' || $action->id == 'create-card' || $action->id == 'save-equipment' || $action->id == 'append-maintenance' || $action->id == 'send-request' || $action->id === 'submit-verification' || $action->id == 'recieved-eq-before' || $action->id == 'recieved-eq-after' || $action->id === 'create-request' || $action->id === 'get-plan-verification' || $action->id === 'print-table' || $action->id === 'save-maintenance' || $action->id === 'save-check')
		{
			$this->enableCsrfValidation = false;
		}
		return parent::beforeAction($action);
	}

	public function actionIndex()
	{
		return $this->render('index');
	}

	public function actionRepair()
	{
		return $this->render('repair');
	}

	public function actionVerification()
	{
		return $this->render('verification');
	}

	public function actionPlan()
	{
		return $this->render('plan');
	}

	public function actionFgis()
	{
		return $this->render('fgis');
	}

	public function actionEquipments()
	{
		return $this->render('equipment');
	}

	public function actionDetails()
	{
		return $this->render('details');
	}

	public function actionGetToday()
	{
		$start = date('Y-m-01');
		$end = date('Y-m-t');
		$total = equipment_total_check::find()->select(['id_type', 'card_number', 'equipment', 'date_next_check', 'is_check'])->where(['between', 'date_next_check', $start, $end])->all();
		return $this->asJson($total);
	}

	public function actionGetVerification()
	{
		if(Yii::$app->request->isGet)
		{
			$checks = view_equipment_check::find()->all();
			$kits = view_equipment_kits::find()->all();
			$kt = array();
			foreach ($checks as $check)
			{
				foreach ($kits as $kit)
				{
					if($check->id === $kit->id_checks)
						$kt[] = array(
							'id_kit_row' => $kit->id_kit_row,
							'id_checks' => $kit->id_checks,
							'equipment' => $kit->equipment,
							'is_received_before' => $kit->is_received_before,
							'is_received_after' => $kit->is_received_after,
							'model' => $kit->model,
							'number' => $kit->number
						);
				}
			$chk[] = array('date_create' => $check->date_create, 'date_submit' => $check->date_submit, 'equipment' => $kt);
			unset($kt);
			}
			return $this->asJson($chk);
		}	
	}

	public function actionGetPlanVerification()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			if($data['type'] === 4)
				$plan = equipment_list_works_plan::find()->where(['between', 'date_maintenance', $data['start'], $data['end']])->all();
			else
				$plan = equipment_total_check::find()->where(['id_type' => $data['type']])->andWhere(['between', 'date_next_check', $data['start'], $data['end']])->all();
			return $this->asJson($plan);
		}	
	}

	public function actionPrintTable()
	{
		//ПЕРЕДЕЛАТЬ
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$input = 'D:/OpenServer/OSPanel/domains/nolims/frontend/web/assets/template/plan.jasper';
			$output = 'D:/OpenServer/OSPanel/domains/nolims/frontend/web/assets/template/';
			// if(!$data['type'])
				// $sql = "date_next_check BETWEEN '". $data['start'] ."' AND '" . $data['end'] . "'";
			// else
			foreach ($data['type'] as $val)
			{
				if(end($data['type']) === $val) $ids .= $val;
				else $ids .= $val . ',';
			}
				$sql = "id_type IN (". $ids .") AND date_next_check BETWEEN '". $data['start'] ."' AND '" . $data['end'] . "'";
			$options = [
				'format' => ['pdf'],
				'params' => ['filter' => $sql],
				'db_connection' => [
				'driver' => 'generic',
				'host' => '192.168.0.55',
				'port' => '3306',
				'database' => 'nolims',
				'username' => 'root',
				'password' => 'K7D4uotKzWersAc3',
				'jdbc_driver' => 'com.mysql.jdbc.Driver',
				'jdbc_url' => 'jdbc:mysql://192.168.0.55/nolims',
				]
			];
			$jasper = new PHPJasper;
			return $this->asJson($jasper->process($input, $output, $options)->execute());
		}
	}

	public function actionSubmitVerification()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$checks = equipment_kit_equipment::find()->where(['id_checks' => $data['id_check'], 'is_received_before' => true])->all();
			$check = equipment_checks::updateAll(['id_status_check' => 2, 'date_submit' => date('Y-m-d')], ['id' => $data['id_check']]);
			$eqs = array();
			foreach ($checks as $check)
				array_push($eqs, $check->id_equipment);
				equipment_equipment::updateAll(['is_check' => true], ['id' => $eqs]);
			return Yii::$app->response->statusCode = 200;
		}
	}

	public function actionRecievedEqBefore()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$kit = equipment_kit_equipment::updateAll(['is_received_before' => true], ['id' => $data['id_kit']]);
			return Yii::$app->response->statusCode = 200;
		}
	}

	public function actionRecievedEqAfter()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$kit = equipment_kit_equipment::updateAll(['is_received_after' => true], ['id' => $data['id_kit']]);
			$kit = equipment_kit_equipment::find()->where(['id' => $data['id_kit']])->all();
			$ch = equipment_checks::find()->where(['id' => $kit[0]->id_checks])->all();
			if($ch->id_status_check != 3)
				equipment_checks::updateAll(['id_status_check' => 3], ['id' => $kit[0]->id_checks]);
			equipment_equipment::updateAll(['is_check' => false], ['id' => $kit[0]->id_equipment]);
			return Yii::$app->response->statusCode = 200;
		}
	}

	public function actionGetMaintenance()
	{
		if(Yii::$app->request->isGet)
		{
			$executor = equipment_executor::find()->all();
			$list_maintenance = equipment_list_maintenance::find()->all();
			$type_maintenance = equipment_type_maintenance::find()->all();
			$main = array('executor' => $executor, 'list_maintenance' => $list_maintenance, 'type_maintenance' => $type_maintenance);
			return $this->asJson($main);
		}	
	}

	public function actionGetMaintenances()
	{
		if(Yii::$app->request->isGet)
		{
			$list = equipment_list_maintenances::find()->all();
			return $this->asJson($list);
		}
	}

	public function actionAppendMaintenance()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$main = new equipment_list_work_maintenance();
			$main->id_equipment = $data['id_equipment'];
			$main->id_type_maintenance = $data['id_type_maintenance'];
			$main->id_maintenance = $data['id_maintenance'];
			$main->id_executor = $data['id_executor'];
			$main->periodicity = $data['periodicity'];
			if($main->save())
				return Yii::$app->response->statusCode = 200;
			else return Yii::$app->response->statusCode = 400;
		}	
	}

	public function actionSaveMaintenance()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			foreach ($data as $dts)
			{
				$plan = new equipment_list_works_plan();
				$plan->id_equipment = $dts['id_equipment'];
				$plan->date_maintenance = $dts['date_maintenance'];
				$plan->id_work_maintenance = $dts['id_work'];
				$plan->save();
			}
			return Yii::$app->response->statusCode = 200;
		}
	}

	public function actionGetDetails()
	{
		if(Yii::$app->request->isGet)
		{
			$eq = equipment_equipment_details::find()->where(['id' => Yii::$app->request->get('id')])->one();
			$maintenance = view_equipment_metrolog_list_work_for_equipment::findAll(['id_equipment' => Yii::$app->request->get('id')]);
			$history_check = equipment_details_history_date_check::findAll(['id_equipment' => Yii::$app->request->get('id')]);
			$current_check = equipment_details_date_check::findOne(['id_equipment' => Yii::$app->request->get('id')]);
			$type = equipment_type::find()->all();
			$of_use = equipment_function_of_use::find()->all();
			$condition_working = equipment_condition_working::find()->where(['id_equipment' => Yii::$app->request->get('id')])->one();
			$history_moving = equipment_moving_history::find()->where(['id_equipment' => Yii::$app->request->get('id')])->all();
			//КОСТЫЛЬ
			if(!$history_moving) $history_moving = null;
			//КОСТЫЛЬ
			if(!$condition_working)
				$condition_working = array('humidity' => null, 'pressure' => null, 'temperature' => null, 'voltage' => null, 'amperage' => null);
			//КОСТЫЛЬ
			if(!$history_check) $history_check = null;
			$types = array('type' => $type, 'function_of_use' => $of_use);
			$main = array('equipment' => $eq, 'history_check' => $history_check, 'current_check' => $current_check, 'history_moving' => $history_moving, 'types' => $types, 'condition_working' => $condition_working, 'maintenance' => $maintenance);
			return $this->asJson($main);
		}
	}

	public function actionSaveEquipment()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			if($data['equipment'])
			{
				$eq = equipment_equipment::find()->where(['id' => $data['id']])->one();
				if($eq)
					foreach ($data['equipment'] as $key => $item)
						$eq[$key] = $item;
				if($eq->save());
					return Yii::$app->response->statusCode = 200;
			}
			if($data['condition_working'])
			{
				$eq = equipment_condition_working::find()->where(['id_equipment' => $data['id']])->one();
				if($eq)
				{
					foreach ($data['condition_working'] as $key => $item)
						$eq[$key] = $item;
					if($eq->save()); return Yii::$app->response->statusCode = 200;
				}
				else
				{
					$eq = new equipment_condition_working();
					$eq->id_equipment = $data['id'];
					foreach ($data['condition_working'] as $key => $item)
						$eq[$key] = $item;
					if($eq->save()); return Yii::$app->response->statusCode = 200;
				}
			}
		}
	}

	public function actionGetEquipments()
	{
		$equipments = view_metrolog_equipment::find()->all();
		return $this->asJson($equipments);
	}

	public function actionGetType()
	{
		$type = equipment_type::find()->all();
		return $this->asJson($type);
	}

	public function actionGetObjectStudy()
	{
		$object = equipment_object_study::find()->all();
		return $this->asJson($object);
	}

	public function actionGetDocType()
	{
		$type = equipment_upload_document_type::find()->all();
		return $this->asJson($type);
	}

	public function actionAppendEquipment()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$equipment = new equipment_equipment();
			$equipment->id_department = $data['id_department'];
			$equipment->id_equipment_type = $data['id_equipment_type'];
			$equipment->number = $data['number'];
			$equipment->title = $data['title'];
			$equipment->model = $data['model'];
			$equipment->serial_number = $data['serial_number'];
			$equipment->manufacturer = $data['manufacturer'];
			$equipment->date_create = $data['date_create'];
			$equipment->inventory_number = $data['inventory_number'];
			$equipment->id_location = $data['id_location'];
			if($equipment->save()) return $this->asJson($equipment);
		}
		return $this->render('append');
	}

	public function actionGetDepartment()
	{
		if(Yii::$app->request->isGet)
		{
			$departments = department::find()->all();
			$locations = location::find()->all();
			$arr = array();
			$location = array();

			foreach ($departments as $dep)
			{
				foreach ($locations as $loc)
				{
					if($dep->id == $loc->id_department)
					{
						$location[] = array(
							'id_location' => $loc->id,
							'cabinet_number' => $loc->cabinet_number,
							'place' => $loc->place,
							'notation' => $loc->notation
						);
					}
				}
				$arr[] = array('id_department' => $dep->id, 'department' => $dep->title, 'locations' => $location);
				unset($location);
			}
			return $this->asJson($arr);
		}
	}

	//ПЕРЕДЕЛАТЬ
	public function actionChangeCheck()
	{
		if(Yii::$app->request->isPost)
		{
			$model = new UploadForm();
			$data = Yii::$app->request->post();
			if($data['is_archive'] === 'true')
			{
				$eq_check = equipment_date_check::findByEqId($data['id_equipment']);
				$eq = equipment_equipment::find()->where(['id' => $data['id_equipment']])->one();
				if($eq_check && $model->upload_file_name = UploadedFile::getInstanceByName('upload_file_name'))
				{
					$eq_check->date_current_check = $data['date_current_check'];
					$eq_check->id_upload_document_type = 11;
					$eq_check->number_document = $data['number_document'];
					$eq_check->upload_file_name = $model->upload_file_name->baseName . '.' . $model->upload_file_name->extension;
					if($eq_check->save())
						if($eq)
						{
							$eq->is_archive = true;
							$eq->is_conservation = false;
							$eq->is_check = false;
							$eq->is_repair = false;
							$eq->is_working = false;
							if($eq->save())
								if ($model->upload()) return Yii::$app->response->statusCode = 200;
								else return Yii::$app->response->statusCode = 400;
						}
				}
				else if (!$eq_check && $model->upload_file_name = UploadedFile::getInstanceByName('upload_file_name'))
				{
					$eq_check = new equipment_date_check();
					$eq_check->id_equipment = $data['id_equipment'];
					$eq_check->date_current_check = $data['date_current_check'];
					// $eq_check->date_next_check = $data['date_current_check'];
					$eq_check->id_upload_document_type = 11;
					$eq_check->number_document = $data['number_document'];
					$eq_check->upload_file_name = $model->upload_file_name->baseName . '.' . $model->upload_file_name->extension;
					if($eq_check->save())
						if($eq)
						{
							$eq->is_archive = true;
							$eq->is_conservation = false;
							$eq->is_check = false;
							$eq->is_repair = false;
							$eq->is_working = false;
							if($eq->save())
								if ($model->upload()) return Yii::$app->response->statusCode = 200;
								else return Yii::$app->response->statusCode = 400;
						}
				}
			}
			else if($data['is_conservation'] === 'true')
			{
				$eq_check = equipment_date_check::findByEqId($data['id_equipment']);
				$eq = equipment_equipment::find()->where(['id' => $data['id_equipment']])->one();
				if($eq_check && $model->upload_file_name = UploadedFile::getInstanceByName('upload_file_name'))
				{
					$eq_check->date_current_check = $data['date_current_check'];
					// $eq_check->date_next_check = $data['date_next_check'];
					$eq_check->id_upload_document_type = 10;
					$eq_check->number_document = $data['number_document'];
					$eq_check->upload_file_name = $model->upload_file_name->baseName . '.' . $model->upload_file_name->extension;
					if($eq_check->save())
						if($eq)
						{
							$eq->is_archive = false;
							$eq->is_conservation = true;
							$eq->is_check = false;
							$eq->is_repair = false;
							$eq->is_working = false;
							if($eq->save())
								if ($model->upload()) return Yii::$app->response->statusCode = 200;
								else return Yii::$app->response->statusCode = 400;
						}
				}
				if (!$eq_check && $model->upload_file_name = UploadedFile::getInstanceByName('upload_file_name'))
				{
					$eq_check = new equipment_date_check();
					$eq_check->id_equipment = $data['id_equipment'];
					$eq_check->date_current_check = $data['date_current_check'];
					$eq_check->id_upload_document_type = 10;
					$eq_check->number_document = $data['number_document'];
					$eq_check->upload_file_name = $model->upload_file_name->baseName . '.' . $model->upload_file_name->extension;
					if($eq_check->save())
						if($eq)
						{
							$eq->is_archive = false;
							$eq->is_conservation = true;
							$eq->is_check = false;
							$eq->is_repair = false;
							$eq->is_working = false;
							if($eq->save())
								if ($model->upload()) return Yii::$app->response->statusCode = 200;
								else return Yii::$app->response->statusCode = 400;
						}
				}
			}
			// else
			// {
				$eq = equipment_date_check::findByEqId($data['id_equipment']);
				$eq_eq = equipment_equipment::find()->where(['id' => $data['id_equipment']])->one();
				if($eq)
				{
					$eq->date_current_check = $data['date_current_check'];
					$eq->date_next_check = $data['date_next_check'];
					$eq->id_upload_document_type = $data['id_upload_document_type'];
					$eq->number_document = $data['number_document'];
					if($model->upload_file_name = UploadedFile::getInstanceByName('upload_file_name'))
						$eq->upload_file_name = $model->upload_file_name->baseName . '.' . $model->upload_file_name->extension;
					if($eq->save())
						$eq_eq->is_check = false;
						if($eq_eq->save())
						{
							if ($model->upload_file_name)
							{
								if($model->upload())
									return Yii::$app->response->statusCode = 200;
							}
							else return Yii::$app->response->statusCode = 200;
						}
				}
				else
				{
					$eq = new equipment_date_check();
					$eq->id_equipment= $data['id_equipment'];
					$eq->date_current_check = $data['date_current_check'];
					$eq->date_next_check = $data['date_next_check'];
					$eq->id_upload_document_type = $data['id_upload_document_type'];
					$eq->number_document = $data['number_document'];
					if($model->upload_file_name = UploadedFile::getInstanceByName('upload_file_name'))
						$eq->upload_file_name = $model->upload_file_name->baseName . '.' . $model->upload_file_name->extension;
					if($eq->save())
						if ($model->upload_file_name)
						{
							if($model->upload())
								return Yii::$app->response->statusCode = 200;
						}
						else return Yii::$app->response->statusCode = 200;
				}
		}
	}

	public function actionSaveCheck()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$eq = equipment_date_check::find()->where(['id_equipment' => $data['id_equipment']])->one();
			// return $this->asJson($eq);
			// if($eq)
			foreach ($data as $key => $item)
			{
				if($key != 'id_equipment' || $key != 'id');
					$eq[$key] = $item;
			}
			if($eq->save());
				return Yii::$app->response->statusCode = 200;
		}
	}

	public function actionSetTag()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$eq = view_metrolog_equipment::updateAll([$data['tag'] => $data['value']], ['id' => $data['eq']]);
			if($eq)
				return Yii::$app->response->statusCode = 200;
		}
	}

	public function actionSetHandoff()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$eq = equipment_equipment::updateAll(['id_department' => $data['id_department_to'], 'id_location' => $data['id_location']], ['id' => $data['id_equipment']]);
			if($eq)
			{
				return Yii::$app->response->statusCode = 200;
			}
		}
	}

	public function actionSendRequest()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$check = new equipment_checks();
			//1 - подготовка 2 - отправлено 3 - получено
			//is_received_before - проверка на получение до отправки
			//is_received_after - проверка на получение после отправки
			$check->id_status_check = 1;
			$check->date_create = date('Y-m-d');
			if($check->save())
				foreach ($data as $key)
				{
					$kit = new equipment_kit_equipment();
					$kit->id_checks = $check->id;
					$kit->id_department = $key['id_department'];
					$kit->id_equipment = $key['id_equipment'];
					$kit->is_received_before = false;
					$kit->is_received_after = false;
					$kit->save();
				}
			return $this->asJson(Yii::$app->request->post());
		}
	}

	public function actionUploadFile()
	{
		//ВМЕСТО UPLOADFILE
		// if(Yii::$app->request->isPost)
		// {
		// 	// return $this->asJson(Yii::$app->request->post());
		// 	$data = Yii::$app->request->post();
		// 	$eq = equipment_date_check::findByEqId($data['id_equipment']);
		// 	if($eq)
		// 	{
		// 		$eq->date_current_check = $data['date_current_check'];
		// 		$eq->date_next_check = $data['date_next_check'];
		// 		$eq->id_upload_document_type = $data['id_upload_document_type'];
		// 		$eq->number_document = $data['number_document'];
		// 		if($eq->save()) return Yii::$app->response->statusCode = 200;
		// 		else Yii::$app->response->statusCode = 400;
		// 	}
		// 	else
		// 	{
		// 		$eq = new equipment_date_check();
		// 		$eq->id_equipment= $data['id_equipment'];
		// 		$eq->date_current_check = $data['date_current_check'];
		// 		$eq->date_next_check = $data['date_next_check'];
		// 		$eq->id_upload_document_type = $data['id_upload_document_type'];
		// 		$eq->number_document = $data['number_document'];
		// 		if($eq->save()) return Yii::$app->response->statusCode = 200;
		// 		else Yii::$app->response->statusCode = 400;			
		// 	}
		// }
	}

	public function actionCreateSticker()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			foreach ($data as $val)
			{
				if(end($data) === $val) $ids .= $val;
				else $ids .= $val . ',';
			}
			$input = 'D:/OpenServer/OSPanel/domains/nolims/frontend/web/assets/template/sticker.jasper';
			$output = 'D:/OpenServer/OSPanel/domains/nolims/frontend/web/assets/template/';
			$options = [
				'format' => ['pdf'],
				'params' => ['id_eq' => 'id_equipment IN ('. $ids .')'],
				'db_connection' => [
				'driver' => 'generic',
				'host' => '192.168.0.55',
				'port' => '3306',
				'database' => 'nolims',
				'username' => 'root',
				'password' => 'K7D4uotKzWersAc3',
				'jdbc_driver' => 'com.mysql.jdbc.Driver',
				'jdbc_url' => 'jdbc:mysql://192.168.0.55/nolims',
				]
			];
			$jasper = new PHPJasper;
			return $this->asJson($jasper->process($input, $output, $options)->execute());
		}
	}

	public function actionCreateCard()
	{

	}

	public function actionCreateRequest()
	{
		if(Yii::$app->request->isPost)
		{
			$data = Yii::$app->request->post();
			$kits = equipment_kit_equipment::find()->select(['id_equipment'])->where(['id_checks' => $data['id_check']])->andWhere(['is_received_before' => 1])->all();
			foreach ($kits as $kit)
			{
				if(end($kits) === $kit) $ids .= $kit->id_equipment;
				else $ids .= $kit->id_equipment . ',';
			}
			$input = 'D:/OpenServer/OSPanel/domains/nolims/frontend/web/assets/template/csm.jasper';
			$output = 'D:/OpenServer/OSPanel/domains/nolims/frontend/web/assets/template/';
			$options = [
				'format' => ['pdf'],
				'params' => ['id_eq' => 'id IN ('. $ids .')', 'dogovor' => $data['dogovor'], 'usr' => Yii::$app->user->identity['login']],
				'db_connection' => [
				'driver' => 'generic',
				'host' => '192.168.0.55',
				'port' => '3306',
				'database' => 'nolims',
				'username' => 'root',
				'password' => 'K7D4uotKzWersAc3',
				'jdbc_driver' => 'com.mysql.jdbc.Driver',
				'jdbc_url' => 'jdbc:mysql://192.168.0.55/nolims',
				]
			];
			$jasper = new PHPJasper;
			return $this->asJson($jasper->process($input, $output, $options)->execute());
		}
	}
}