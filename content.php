<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.html.parameter' );
class plgPhocaPDFContent extends JPlugin
{
	function onBeforeCreatePDFContent(&$content) {
		
		$content->content = '';
		// load plugin params info
	 	$plugin  = JPluginHelper::getPlugin('phocapdf', 'content');		
		$pluginP = new JRegistry(); 
		$pluginP->loadString($plugin->params);
		
		$content->margin_top		= $pluginP->get('margin_top', 33);
		$content->margin_left		= $pluginP->get('margin_left', 15);
		$content->margin_right		= $pluginP->get('margin_right', 15);
		$content->margin_bottom		= $pluginP->get('margin_bottom', 25);
		$content->site_font_color	= $pluginP->get('site_font_color', '#000000');
		$content->site_cell_height	= $pluginP->get('site_cell_height', 1.2);
		
		$content->font_type			= $pluginP->get('font_type', 'helvetica');
		$content->page_format		= $pluginP->get('page_format', 'A4');
		$content->page_orientation	= $pluginP->get('page_orientation', 'L');
		
		$content->header_data		= $pluginP->get('header_data', '');
		$content->header_font_type	= $pluginP->get('header_font_type', 'helvetica');
		$content->header_font_style	= $pluginP->get('header_font_style', '');
		$content->header_font_size	= $pluginP->get('header_font_size', 10);
		$content->header_margin		= $pluginP->get('header_margin', 5);
		
		//administrator\components\com_phocapdf\helpers\phocapdfcontenttcpdf.php
		//$content->footer_data		= $pluginP->get('footer_data', '');
		$content->footer_font_type	= $pluginP->get('footer_font_type', 'helvetica');
		$content->footer_font_style	= $pluginP->get('footer_font_style', '');
		$content->footer_font_size	= $pluginP->get('footer_font_size', 10);
		$content->footer_margin		= $pluginP->get('footer_margin', 15);
		
		$content->pdf_name			= $pluginP->get('pdf_name', 'Phoca PDF');
		$content->pdf_destination	= $pluginP->get('pdf_destination', 'S');
		$content->image_scale		= $pluginP->get('image_scale', 1);
		$content->display_plugin	= $pluginP->get('display_plugin', 0);
		$content->display_image		= $pluginP->get('display_image', 1);
		$content->use_cache			= $pluginP->get('use_cache', 0);
		
		
		//Extra values
		if ((int)$content->site_cell_height > 3) {
			$content->site_cell_height = 3;
		}
		if ((int)$content->margin_top > 200) {
			$content->margin_top = 200;
		}
		if ((int)$content->margin_left > 50) {
			$content->margin_left = 50;
		}
		if ((int)$content->margin_right > 50) {
			$content->margin_right = 50;
		}
		if ((int)$content->margin_bottom > 150) {
			$content->margin_bottom = 150;
		}
		if ((int)$content->header_font_size > 30) {
			$content->header_font_size = 30;
		}
		if ((int)$content->footer_font_size > 30) {
			$content->footer_font_size = 30;
		}
		if ((int)$content->header_margin > 50) {
			$content->header_margin = 50;
		}
		if ((int)$content->footer_margin > 50) {
			$content->footer_margin = 50;
		}
		if ((int)$content->image_scale < 0.5) {
			$content->image_scale = 0.5;
		}
		
		
		return true;
	}
	
	
	function onBeforeDisplayPDFContent(&$pdf, &$content, &$document) {
		
		if (isset($document->_article_title) && $document->_article_title != '') {
			$pdf->SetTitle($document->_article_title);
			$content->pdf_name = $document->_article_title;
		} else {
			$pdf->SetTitle($document->getTitle());
			
		}
	

		$pdf->SetSubject($document->getDescription());
		$pdf->SetKeywords($document->getMetaData('keywords'));
	
		/*
		 * Specific Plugin code for Header
		 * Header is set here in system plugin (Phoca PDF Content Plugin) because we need title and header data
		 * Footer is set in helper of system plugin (Phoca PDF Component) because we need TCPDF data (pagination)
		 */
		if ($content->header_data != '') {
			// Plugin code
			//$content->header_data = str_replace('{phocapdftitle}', $document->title, $content->header_data);
			$content->header_data = str_replace('{phocapdftitle}', $document->_article_title, $content->header_data);
			$content->header_data = str_replace('{phocapdfheader}', $document->_header, $content->header_data);
			
			//$content->header_data = str_replace(utf8_encode("<p>�</p>"), '<p></p>', $content->header_data);
			$content->header_data = str_replace(array(utf8_encode(chr(11)), utf8_encode(chr(160))), ' ', $content->header_data);
			
			
			
			$pdf->setHeaderData('' , 0, '', $content->header_data);
		} else {
			
			if (isset($document->_article_title) && $document->_article_title != '') {
				$pdf->setHeaderData('' , 0, $document->_article_title, $document->getHeader());
			} else {
				$pdf->setHeaderData('' , 0, $document->getTitle(), $document->getHeader());
			}
		}
	
		//administrator\components\com_phocapdf\helpers\phocapdfcontenttcpdf.php
		//if ($content->footer_data != '') {
		//	$content->footer_data = str_replace(utf8_encode("<p>�</p>"), '<p></p>', $content->footer_data);
		//}
		
		$pdf->setHeaderFont(array($content->header_font_type, $content->header_font_style, $content->header_font_size));
		
		
		$lang = JFactory::getLanguage();
		$font = $content->font_type;
		$pdf->setRTL($lang->isRTL());

	
		$pdf->setFooterFont(array($content->footer_font_type, $content->footer_font_style, $content->footer_font_size));
		// Initialize PDF Document
		//$pdf->AliasNbPages();
		$pdf->AddPage();
		
		//$dF = $document->getBuffer();
		// The space must be copied directly from editor and the file must be saved as ANSI
		//$documentOutput = str_replace(utf8_encode("<p>�</p>"), '<p></p>', $dF);
		
		if ($content->display_plugin == 0) {
			
			$dF = $document->getArticleText();	
  			
			$documentOutput = str_replace(array(utf8_encode(chr(11)), utf8_encode(chr(160))), ' ', $dF);
			
			$pattern 		= '/\{(.*)\}/Ui';
			$replacement 	= '';
			$documentOutput = preg_replace($pattern, $replacement, $documentOutput);
			
			
		} else {
			//$dF = $document->getBuffer();
			
			$documentOutputArray 	= $document->getBuffer();
			$dF			= '';
			
			
			if (isset($documentOutputArray['component']) && is_array($documentOutputArray)) {
				foreach ($documentOutputArray['component'] as $v) {
					$dF .= $v;
				}
			} else {
				$dF .=	(string)$documentOutputArray;
			}
			
			$documentOutput = str_replace(array(utf8_encode(chr(11)), utf8_encode(chr(160))), ' ', $dF);
		}
		
		if ($content->display_image == 0) {
			$pattern 		= '/<img(.*)>/Ui';
			$replacement 	= '';
			$documentOutput = preg_replace($pattern, $replacement, $documentOutput);
		}
		
		
		// Build the PDF Document string from the document buffer
		$pdf->writeHTML($documentOutput , true);
		
		return true;
	}
}
?>