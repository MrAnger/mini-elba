<?php
namespace frontend\controllers;

use common\components\PaymentParser1c;
use common\helpers\InvoiceHelper;
use common\helpers\PaymentHelper;
use common\models\Contractor;
use common\models\Invoice;
use common\models\InvoiceItem;
use common\models\Payment;
use common\models\PaymentData;
use common\models\PaymentLinkToInvoice;
use common\models\User;
use frontend\models\PaymentSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * @author MrAnger
 */
class PaymentController extends BaseController {
	public $enableCsrfValidation = false;

	public function actionIndex() {
		$searchModel = new PaymentSearch();

		$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

		$dataProvider->sort->defaultOrder = [
			'date' => SORT_DESC,
		];

		$dataProvider->pagination->pageSize = 30;

		return $this->render('index', [
			'searchModel'  => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

	public function actionCreate() {
		$request = Yii::$app->request;

		$model = new Payment([
			'user_id' => Yii::$app->user->id,
		]);

		if ($model->load($request->post()) && $model->save()) {
			Yii::$app->session->addFlash('success', 'Поступление успешно создано.');

			return $this->redirect(['view', 'id' => $model->id]);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	public function actionUpdate($id) {
		$request = Yii::$app->request;

		$model = $this->findModel($id);

		if (!PaymentHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		if ($model->load($request->post()) && $model->save()) {
			Yii::$app->session->addFlash('success', 'Поступление успешно изменено.');

			return $this->redirect(['view', 'id' => $model->id]);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	public function actionView($id) {
		$model = $this->findModel($id);

		if (!PaymentHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		return $this->render('view', [
			'model' => $model,
		]);
	}

	public function actionDelete($id) {
		$model = $this->findModel($id);

		if (!PaymentHelper::isAccessAllowed($model))
			throw new ForbiddenHttpException;

		if (PaymentHelper::isAvailableDelete($model)) {
			$model->delete();

			Yii::$app->session->addFlash('success', 'Поступление успешно удалено.');

			return $this->redirect(Yii::$app->request->referrer);
		}

		throw new MethodNotAllowedHttpException;
	}

	public function actionGetInvoiceLinkData($paymentId) {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$paymentModel = $this->findModel($paymentId);

		if (!PaymentHelper::isAccessAllowed($paymentModel))
			throw new ForbiddenHttpException;

		// Получаем список счетов, которые можно связать с этим поступлением
		$invoiceList = [];
		// Сначала добавляем уже привязанные счета
		foreach ($paymentModel->invoiceLinks as $link) {
			$invoiceList[] = [
				'id'         => $link->invoice->id,
				'name'       => $link->invoice->name,
				'total_paid' => $link->invoice->total_paid,
				'summary'    => $link->invoice->summary,
				'linked'     => true,
				'linked_sum' => $link->sum,
				'invoiceUrl' => Url::to(['/invoice/view', 'id' => $link->invoice->id]),
			];
		}
		// Далее добавляем те счета, которые можно привязать к этому поступлению
		$invoices = InvoiceHelper::applyAccessByUser(Invoice::find()->where([
			'AND',
			['=', 'contractor_id', $paymentModel->contractor_id],
			['=', 'is_paid', 0],
			['not in', 'id', ArrayHelper::getColumn($invoiceList, 'id')],
		])
			->orderBy(['created_at' => SORT_DESC])
		)
			->all();

		foreach ($invoices as $invoice) {
			/** @var Invoice $invoice */
			$invoiceList[] = [
				'id'         => $invoice->id,
				'name'       => $invoice->name,
				'total_paid' => $invoice->total_paid,
				'summary'    => $invoice->summary,
				'linked'     => false,
				'linked_sum' => 0,
				'invoiceUrl' => Url::to(['/invoice/view', 'id' => $invoice->id]),
			];
		}

		$output = [
			'payment'     => $paymentModel,
			'invoiceList' => $invoiceList,
		];

		return $output;
	}

	public function actionInvoiceLinkForm() {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$request = Yii::$app->request;

		$output = [
			'state'  => true,
			'errors' => [],
		];

		$paymentModel = $this->findModel($request->post('paymentId'));

		if (!PaymentHelper::isAccessAllowed($paymentModel))
			throw new ForbiddenHttpException;

		$linkedMap = $request->post('linkedInvoices', []);

		// Проводим валидацию всех переданных данных
		$paymentTotalLinkedSum = 0;
		foreach ($linkedMap as $invoiceId => $linkedSum) {
			$invoiceModel = $this->findInvoiceModel($invoiceId);

			if (!InvoiceHelper::isAccessAllowed($invoiceModel))
				throw new ForbiddenHttpException;

			$invoiceTotalPaid = $invoiceModel->total_paid;

			// Проверяем, нет ли существующей линковки данного поступления с текущим счетом
			// Если есть, то уменьшаем оплаченую сумму на ранее сохраненное значение
			$oldPaymentLink = $paymentModel->getInvoiceLink($invoiceModel->id);
			if ($oldPaymentLink !== null) {
				$invoiceTotalPaid -= $oldPaymentLink->sum;
			}

			if ($invoiceTotalPaid + $linkedSum > $invoiceModel->summary) {
				$output['state'] = false;
				$output['errors'][] = "Указана слишком большая сумма привязки для сча $invoiceModel->name";
			}

			$paymentTotalLinkedSum += $linkedSum;
		}

		if ($paymentTotalLinkedSum > $paymentModel->income) {
			$output['state'] = false;
			$output['errors'][] = "Общая сумма связки со счетами превышает объем средств поступления.";
		}

		// Если валидация прошла успешно, то сохраняем переданные данные
		if ($output['state']) {
			$transaction = Yii::$app->db->beginTransaction();

			try {
				// Получаем список айдишников счетов, которые удалятся полностью, что бы скорректировать данные оплаты счетов
				$invoiceIdListToDelete = [];
				foreach (ArrayHelper::getColumn($paymentModel->invoiceLinks, 'invoice_id') as $invoiceId) {
					if (array_search($invoiceId, array_keys($linkedMap)) === false) {
						$invoiceIdListToDelete[] = $invoiceId;

						PaymentLinkToInvoice::deleteAll([
							'payment_id' => $paymentModel->id,
							'invoice_id' => $invoiceId,
						]);
					}
				}

				// Корректируем в удаляемых счетах информацию об оплате
				$this->invoiceCorrection($invoiceIdListToDelete);

				foreach ($linkedMap as $invoiceId => $linkedSum) {
					$paymentLink = $paymentModel->getInvoiceLink($invoiceId);

					if ($paymentLink === null) {
						$paymentLink = new PaymentLinkToInvoice([
							'payment_id' => $paymentModel->id,
							'invoice_id' => $invoiceId,
						]);
					}

					$paymentLink->sum = $linkedSum;

					$paymentLink->save();
				}

				// Корректируем в счетах информацию об оплате
				$this->invoiceCorrection(array_keys($linkedMap));

				$transaction->commit();

				return $output;
			} catch (\Exception $e) {
				$transaction->rollBack();

				throw $e;
			}
		}

		return $output;
	}

	/**
	 * @param integer[] $invoiceIdList
	 */
	protected function invoiceCorrection($invoiceIdList) {
		foreach ($invoiceIdList as $invoiceId) {
			$invoice = $this->findInvoiceModel($invoiceId);

			$totalLinkedSum = PaymentLinkToInvoice::find()
				->where([
					'invoice_id' => $invoice->id,
				])
				->sum('sum');

			$isPaid = 0;
			if ($totalLinkedSum >= $invoice->summary)
				$isPaid = 1;

			$invoice->updateAttributes([
				'total_paid' => $totalLinkedSum,
				'is_paid'    => $isPaid,
			]);

			// Теперь можно пройтись по позициям счета и проверить/проставить данные об оплате конкретной позиции
			if ($invoice->is_paid) {
				foreach ($invoice->items as $item) {
					$item->updateAttributes([
						'is_paid'    => 1,
						'total_paid' => $item->summary,
					]);
				}
			} else {
				$invoiceTotalPaid = $invoice->total_paid;

				// Сначала получаем и обрабатываем позиции помеченные как оплаченные полностью
				/** @var InvoiceItem[] $fullPaidPositions */
				$fullPaidPositions = $invoice->getItems()
					->andWhere(['=', 'is_paid', 1])
					->all();

				foreach ($fullPaidPositions as $position) {
					if ($invoiceTotalPaid >= $position->summary) {
						$invoiceTotalPaid -= $position->summary;
						continue;
					}

					$position->updateAttributes([
						'is_paid'    => 0,
						'total_paid' => $invoiceTotalPaid,
					]);

					$invoiceTotalPaid -= $invoiceTotalPaid;

					if ($invoiceTotalPaid < 0)
						$invoiceTotalPaid = 0;
				}

				// Теперь получаем и обрабатываем позиции помеченные как оплаченные частично
				/** @var InvoiceItem[] $notFullPaidPositions */
				$notFullPaidPositions = $invoice->getItems()
					->andWhere([
						'AND',
						['=', 'is_paid', 0],
						['<>', 'total_paid', 0],
						['not in', 'id', ArrayHelper::getColumn($fullPaidPositions, 'id')],
					])
					->all();

				foreach ($notFullPaidPositions as $position) {
					if ($invoiceTotalPaid >= $position->total_paid) {
						$invoiceTotalPaid -= $position->summary;
						continue;
					}

					$position->updateAttributes([
						'total_paid' => $invoiceTotalPaid,
					]);

					$invoiceTotalPaid -= $invoiceTotalPaid;

					if ($invoiceTotalPaid < 0)
						$invoiceTotalPaid = 0;
				}
			}
		}
	}

	public function actionImportFromFile() {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$request = Yii::$app->request;
		/** @var User $user */
		$user = Yii::$app->user->identity;

		$formatter = Yii::$app->formatter;

		$output = [
			'state'  => true,
			'errors' => [],
		];

		$uploadedFile = UploadedFile::getInstanceByName('file');

		/** @var PaymentParser1c $parser */
		$parser = Yii::$app->get('paymentParser1c');

		$parseResult = $parser->parse($uploadedFile->tempName);

		// Получаем только поступления для текущего пользователя
		/** @var PaymentData[] $incomes */
		$incomes = [];
		foreach ($parseResult as $item) {
			if ($item->recipient_inn == $user->profile->inn) {
				$incomes[] = $item;
			}
		}

		// Добавляем спарсенные поступления в БД
		$transaction = Yii::$app->db->beginTransaction();

		$countTotal = count($incomes);
		$countSuccessImporting = 0;

		try {
			foreach ($incomes as $income) {
				if (empty($income->payer_inn))
					continue;

				// Сначала пробуем найти контрагента по указанному ИНН
				/** @var Contractor $contractor */
				$contractor = Contractor::findOne(['user_id' => $user->id, 'inn' => $income->payer_inn]);

				if ($contractor === null) {
					$contractor = new Contractor([
						'user_id' => $user->id,
						'inn'     => $income->payer_inn,
						'name'    => ((!empty($income->payer_name1)) ? $income->payer_name1 : $income->payer_name),
					]);

					if (!$contractor->save()) {
						continue;
					}
				}

				$payment = new Payment([
					'user_id'         => $user->id,
					'contractor_id'   => $contractor->id,
					'date'            => date_format(date_create_from_format("d.m.Y", $income->date), "Y-m-d"),
					'document_number' => $income->document_number,
					'income'          => $income->income,
					'description'     => $income->description,
				]);

				if ($payment->save()) {
					$countSuccessImporting++;
				}
			}

			$transaction->commit();

			Yii::$app->session->addFlash('success', "Импорт завершен. Поступлений прочитано: " . $formatter->asInteger($countTotal) . ". Успешно импортированно: " . $formatter->asInteger($countSuccessImporting) . ".");

			return $output;
		} catch (\Exception $e) {
			$transaction->rollBack();

			throw $e;
		}
	}

	/**
	 * @param mixed $pk
	 *
	 * @return Payment
	 *
	 * @throws NotFoundHttpException
	 */
	protected function findModel($pk) {
		if (($model = Payment::findOne($pk)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested payment does not exist.');
		}
	}

	/**
	 * @param mixed $pk
	 *
	 * @return Invoice
	 *
	 * @throws NotFoundHttpException
	 */
	protected function findInvoiceModel($pk) {
		if (($model = Invoice::findOne($pk)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested invoice does not exist.');
		}
	}
}
