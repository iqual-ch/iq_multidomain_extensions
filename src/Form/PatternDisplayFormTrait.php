<?php

namespace Drupal\iq_multidomain_extensions\Form;

use Drupal\Component\Utility\SortArray;
use Drupal\ui_patterns\Plugin\PatternSourceBase;
use \Drupal\ui_patterns\Form\PatternDisplayFormTrait as OriginalTrait;

/**
 * Trait PatternDisplayFormTrait.
 *
 * @property \Drupal\ui_patterns\UiPatternsManager $patternsManager
 * @property \Drupal\ui_patterns\UiPatternsSourceManager $sourceManager
 * @method \Drupal\Core\StringTranslation\TranslatableMarkup t($string, array $args = [], array $options = [])
 *
 * @package Drupal\ui_patterns\Form
 */
trait PatternDisplayFormTrait {
	use OriginalTrait;

	/**
	 * Build pattern display form.
	 *
	 * @param array $form
	 *   Form array.
	 * @param string $tag
	 *   Source field tag.
	 * @param array $context
	 *   Plugin context.
	 * @param array $configuration
	 *   Default configuration coming form the host form.
	 */
	public function buildPatternDisplayForm(array &$form, $tag, array $context, array $configuration) {


		$pattern_options = $this->patternsManager->getPatternsOptions();
		$definitions = $this->patternsManager->getDefinitions();
		$config = \Drupal::config('system.theme');
		$defaultTheme = $config->get('default');

		foreach($definitions as $id => $definition) {
			if ($definition->getProvider()!= $defaultTheme) {
				unset($pattern_options[$id]);
				unset($definitions[$id]);
			}
		}
		$form['pattern'] = [
			'#type' => 'select',
			'#empty_value' => '_none',
			'#title' => $this->t('Pattern'),
			'#options' => $pattern_options,
			'#default_value' => isset($configuration['pattern']) ? $configuration['pattern'] : NULL,
			'#required' => TRUE,
			'#attributes' => ['id' => 'patterns-select'],
		];

		/** @var \Drupal\ui_patterns\Definition\PatternDefinition $definition */
		foreach ($definitions as $pattern_id => $definition) {
			if ($definition->hasVariants()) {
				$form['pattern_variant'] = [
					'#type' => 'select',
					'#title' => $this->t('Variant'),
					'#options' => $definition->getVariantsAsOptions(),
					'#default_value' => isset($configuration['pattern_variant']) ? $configuration['pattern_variant'] : NULL,
					'#weight' => 0,
					'#states' => [
						'visible' => [
							'select[id="patterns-select"]' => ['value' => $pattern_id],
						],
					],
				];
			}

			$form['pattern_mapping'][$pattern_id] = [
				'#type' => 'container',
				'#weight' => 1,
				'#states' => [
					'visible' => [
						'select[id="patterns-select"]' => ['value' => $pattern_id],
					],
				],
				'settings' => $this->getMappingForm($pattern_id, $tag, $context, $configuration),
			];
		}
	}


}
