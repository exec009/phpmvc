<?php
namespace CONTROLLERS;
use \CORE\MVC\View;
use \CORE\MVC\PartialView;
use CORE\MVC\Controller;
use CORE\MVC\IController;
use MODELS\ADMIN\Admin;
class DefaultController extends Controller implements IController
{
    public function home():View
    {
        return $this->view();
    }
    public function error():View
    {
        return $this->view();
    }
	private function sampleDataTable(): View
    {
        $txs = \MODELS\TRANSACTION\Transaction::find()->limit(0,10)
        ->processForDataTable([
            'Id',
			'TransactionId' => '<b style="font-size:10px">{{TransactionId}}</b>',
            'ToAddress' => function($toAddress) {
                $walletAddress = \MODELS\WALLET\WalletAddress::find()->where(['Address', '=', $toAddress])->single();
                $User = \MODELS\USER\User::init();
                if($walletAddress != null)
                {
                    $User = $walletAddress->getWallet()->getUser();
                }
                return $User->getName();
            },
            'Amount',
            'Currency' => function ($currency) { return $currency->getName(); },
            'Date' => function ($date) { return (string)$date; },
            'Confirmation' => function ($blockHeight, $currency)  { return ($currency->getLastBlock() - $blockHeight);  }
        ]);
		return $this->content($txs);
    }

}