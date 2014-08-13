<?php

if (!defined('_PS_VERSION_'))
  exit;

class MineWhat extends Module {

  /**
   * Plugin configuration
   *
   */
  public function __construct() {

    $this->name = 'minewhat';
    $this->tab = 'analytics_stats';
    $this->version = '1.3';
    $this->author = 'MineWhat';
    $this->need_instance = 0;
    $this->bootstrap = true;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6');
    $this->dependencies = array('blockcart');

    parent::__construct();

    $this->displayName = $this->l('MineWhat Analytics');
    $this->description = $this->l('A smarter way to run a data driven online store.');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    if (!Configuration::get('MYMODULE_NAME'))
      $this->warning = $this->l('No name provided');
  }

  /**
   * Install MineWhat Prestashop Plugin
   *
   */
  public function install() {
    if (Shop::isFeatureActive())
      Shop::setContext(Shop::CONTEXT_ALL);

    return parent::install()
      && $this->registerHook('displayHeader')  // base script
      && $this->registerHook('displayLeftColumnProduct')  // product script
      // && $this->registerHook('cart')  // addtocart script
      && $this->registerHook('displayShoppingCart')  // bag script
      // && $this->registerHook('orderConfirmation')  // buy script
      && $this->registerHook('displayOrderConfirmation')  // buy script
      && Configuration::updateValue('MYMODULE_NAME', 'minewhat');
  }

  /**
   * Uninstall MineWhat Prestashop Plugin
   *
   */
  public function uninstall() {
    return parent::uninstall() && Configuration::deleteByName('MYMODULE_NAME');
  }

  /**
   * Put the MineWhat base script in the head tag
   *
   * @param array $params variables from the front end
   */
  public function hookDisplayHeader($params) {
    $MW_ENABLE = Configuration::get('MW_ENABLE');
    if($MW_ENABLE) {
      $MW_DOMAIN_ID = Configuration::get('MW_DOMAIN_ID');
      $org = split("_", $MW_DOMAIN_ID)[0];
      $this->context->smarty->assign(
        array(
            'org'  => $org
            )
      );
      return $this->display(__FILE__, 'baseScript.tpl');
    } else {
      return null;
    }
  }

  /**
    * Triggered when a user views a product page
    *
    * @param array $params variables from the front end
    */
  public function hookDisplayLeftColumnProduct($params) {
    $MW_ENABLE = Configuration::get('MW_ENABLE');
    if($MW_ENABLE) {
      $product_id = Tools::getValue('id_product');
      $product = new Product($product_id,
                              $this->context->language->id,
                              $this->context->shop->id);
      $mwdata = array();
      $mwdata['product'] = array();
      $mwdata['product']['id'] = $product_id;
      $mwdata['product']['sku'] = $product->reference;
      $mwdata['product']['price'] = $product->price;
      $this->context->smarty->assign(
        array(
            'product_id'  => $product_id,
            'mwdata'      => json_encode($mwdata)
            )
      );
      return $this->display(__FILE__, 'product.tpl');
    } else {
      return null;
    }
  }

 /**
   * Triggered when a user navigates to cart page
   *
   * @param array $params variables from the front end
   */
  public function hookDisplayShoppingCart($params) {
    $MW_ENABLE = Configuration::get('MW_ENABLE');
    if($MW_ENABLE) {
      $products = $params['cart']->getProducts(true);
      $formattedProducts = array();
      foreach($products as $product) {
        $formattedProducts[] = array(
          pid => $product["id_product"],
          qty => $product["quantity"],
          sku => $product["reference"],
          price => $product["price"]
        );
      }
      $bag = array(
         products => $formattedProducts
      );
      $this->context->smarty->assign(
        array(
            'bag' => json_encode($bag)
            )
      );
      return $this->display(__FILE__, 'addtocart.tpl');
    } else {
      return null;
    }
  }

  /**
   * Triggered when a user addtocart
   *
   * @param array $params variables from the front end
   */
  public function hookCart($params) {

  }

  /**
   * Triggered when a user makes a purchase
   *
   * @param array $params variables from the front end
   */
  public function hookDisplayOrderConfirmation($params) {
    $MW_ENABLE = Configuration::get('MW_ENABLE');
    if($MW_ENABLE) {
      return $this->display(__FILE__, 'buy.tpl');
    } else {
      return null;
    }
  }

  /**
   *
   * Configuration page
   */
  public function getContent() {

    $html = '';

		$display_slider = false;

		if ($display_slider) {
			$slides = array(
				'MineWhat.png' => $this->l('Go to https://app.minewhat.com/')
			);
			$first_slide = key($slides);

			$html .= '
			<a id="screenshots_button" href="#screenshots"><button class="btn btn-default"><i class="icon-question-sign"></i> How to configure MineWhat Analytics</button></a>
			<div style="display:none">
				<div id="screenshots" class="carousel slide">
					<ol class="carousel-indicators">';
				$i = 0;
			foreach ($slides as $slide => $caption)
				$html .= '<li data-target="#screenshots" data-slide-to="'.($i++).'" '.($slide == $first_slide ? 'class="active"' : '').'></li>';
			$html .= '
					</ol>
					<div class="carousel-inner">';
			foreach ($slides as $slide => $caption)
				$html .= '
						<div class="item '.($slide == $first_slide ? 'active' : '').'">
							<img src="'.$this->_path.'screenshots/'.$slide.'" style="margin:auto">
							<div style="text-align:center;font-size:1.4em;margin-top:10px;font-weight:700">
								'.$caption.'
							</div>
							<div class="clear">&nbsp;</div>
						</div>';
			$html .= '
					</div>
					<a class="left carousel-control" href="#screenshots" data-slide="prev">
						<span class="icon-prev"></span>
					</a>
					<a class="right carousel-control" href="#screenshots" data-slide="next">
						<span class="icon-next"></span>
					</a>
				</div>
			</div>
			<div class="clear">&nbsp;</div>
			<script type="text/javascript">
				$(document).ready(function(){
					$("a#screenshots_button").fancybox();
					$("#screenshots").carousel({interval:false});
					$("ol.carousel-indicators").remove();
				});
			</script>';
		}

    if (Tools::isSubmit('submitSettings')) {

      $MW_ENABLE = Tools::getValue('MW_ENABLE');
      $MW_DOMAIN_ID = Tools::getValue('MW_DOMAIN_ID');

      if(strlen($MW_DOMAIN_ID) <= 0) {
          $MW_ENABLE = '0';
      }

      Configuration::updateValue('MW_ENABLE', $MW_ENABLE);
      Configuration::updateValue('MW_DOMAIN_ID', $MW_DOMAIN_ID);

      Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&conf=6');

  	}

    return $html.$this->renderForm();

	}

  public function renderForm() {
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Domain ID'),
						'name' => 'MW_DOMAIN_ID'
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Enable'),
						'name' => 'MW_ENABLE',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						),
					)
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitSettings';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

  public function getConfigFieldsValues() {
    return array(
      'MW_DOMAIN_ID' => Tools::getValue('MW_DOMAIN_ID', Configuration::get('MW_DOMAIN_ID')),
      'MW_ENABLE' => Tools::getValue('MW_ENABLE', Configuration::get('MW_ENABLE'))
    );
  }

}

?>
