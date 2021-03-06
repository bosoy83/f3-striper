<?php
namespace Striper\Site\Controllers;

class PaymentRequest extends \Dsc\Controller
{

    protected function getModel()
    {
        $model = new \Striper\Models\PaymentRequests;
        return $model;
    }

    public function read()
    {
        $id = $this->inputfilter->clean($this->app->get('PARAMS.id'), 'alnum');
        $number = $this->inputfilter->clean($this->app->get('PARAMS.number'), 'cmd'); // these are like slugs, and can have a hyphen in them
        
        $model = $this->getModel();
        if ($id)
        {
            $model->setState('filter.id', $id);
        }
        elseif ($number)
        {
            $model->setState('filter.number', $number);
        }
        else {
            \Dsc\System::instance()->addMessage("Invalid Item", 'error');
            $this->app->reroute('/');        	
        }
        
        try
        {
            $paymentrequest = $model->getItem();
            if (empty($paymentrequest->id)) 
            {
            	throw new \Exception;
            }
        }
        catch (\Exception $e)
        {
            \Dsc\System::instance()->addMessage("Invalid Item", 'error');
            $this->app->reroute('/');
            return;
        }
        
        $this->app->set('meta.title', $paymentrequest->title);
        $this->app->set('paymentrequest', $paymentrequest);
        $this->app->set('settings', \Striper\Models\Settings::fetch());
        
        echo $this->theme->render('Striper/Site/Views::paymentrequest/read.php');
    }

    public function charge()
    {
        $id = $this->inputfilter->clean($this->app->get('PARAMS.id'), 'alnum');
    
        $request = $this->getModel()->setState('filter.id', $id)->getItem();
        $settings = \Striper\Models\Settings::fetch();
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here https://manage.stripe.com/account
        \Stripe::setApiKey($settings->{$settings->mode.'.secret_key'});
        
        // Get the credit card token submitted by the form
        $token = $this->inputfilter->clean($this->app->get('POST.stripeToken'), 'string');
        
        // Create the charge on Stripe's servers - this will charge the user's card
        try
        {
            $charge = \Stripe_Charge::create(array(
                "amount" => $request->amountForStripe(), // amount in cents, again
                "currency" => "usd",
                "card" => $token,
                "description" => $request->{'client.email'}
            ));
            // this needs to be created empty in model
          
            $request->acceptPayment($charge);
            // SEND email to the client
            $request->sendChargeEmailClient($charge);
            $request->sendChargeEmailAdmin($charge);
            
            $this->app->set('charge', $charge);
            $this->app->set('paymentrequest', $request);
            
            $view = \Dsc\System::instance()->get('theme');
            echo $view->render('Striper/Site/Views::paymentrequest/success.php');
        }
        catch (\Stripe_CardError $e)
        {
            
            // The card has been declined
            $view = \Dsc\System::instance()->get('theme');
            echo $view->render('Striper/Site/Views::paymentrequest/index.php');
        }
    }
}