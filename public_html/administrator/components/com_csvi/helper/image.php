<?php
/**
 * @package     CSVI
 * @subpackage  Images
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Image helper.
 *
 * @package     CSVI
 * @subpackage  Images
 * @since       6.0
 */
class CsviHelperImage
{
	/**
	 * Template helper
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	private $template = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	private $log = null;

	/**
	 * CSVI helper
	 *
	 * @var    CsviHelperCsvi
	 * @since  6.0
	 */
	private $csvihelper = null;

	/**
	 * List of known mime types
	 *
	 * @var    array
	 * @since  3.0
	 */
	private $mimeTypes = array();

	/**
	 * List of known image types
	 *
	 * @var    array
	 * @since  3.0
	 */
	private $imageTypes = array();

	/** @var array holds the mime types it found */
	private $_found_mime_type = array();
	/** @var array contains all the image data for processing */
	private $_imagedata = array();

	/**	@var int $bg_red 0-255 - red color variable for background filler */
	private $bg_red = 0;
	/**	@var int $bg_green 0-255 - green color variable for background filler */
	private $bg_green = 0;
	/** @var int $bg_blue 0-255 - blue color variable for background filler */
	private $bg_blue = 0;
	/**	@var int $maxSize 0-1 - true/false - should thumbnail be filled to max pixels */
	private $maxSize = false;
	/** @var string $file the original file */
	private $file = null;
	/** @var string $file_extension the extension of the original file */
	private $file_extension = null;
	/** @var string $file_out the name of the file to be created */
	private $file_out = null;
	/** @var string $file_out_extension the extension of the file to be created */
	public $file_out_extension = null;
	/** @var int $file_out_width the width of the file to be generated */
	private $file_out_width = 0;
	/** @var int $file_out_height the height of the file to be generated */
	private $file_out_height = 0;

	/**
	 * Constructor.
	 *
	 * @since   6.0
	 */
	public function __construct(CsviHelperTemplate $template, CsviHelperLog $log, CsviHelperCsvi $csvihelper)
	{
		$this->template = $template;
		$this->log = $log;
		$this->csvihelper = $csvihelper;

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$this->loadMimeTypes();
		$this->loadImageTypes();
	}

	/**
	 * Check if the given file is an image.
	 *
	 * @param   string  $file    Full path to file to check
	 * @param   bool    $remote  True if the file to check is a remote file
	 *
	 * @return  bool  True if file is image | False if file is not an image.
	 *
	 * @since   3.0
	 */
	public function isImage($file, $remote=false)
	{
		$mime_type = $this->findMimeType($file, $remote);

		if ($mime_type)
		{
			foreach ($this->imageTypes as $type)
			{
				if ($type['mime_type'] == $mime_type)
				{
					return true;
				}
			}
		}

		// If we get here, no image type has been found
		return false;
	}

	/**
	 * Check a file for its mime type.
	 *
	 * @param   string  $filename  The full location of the file to check
	 * @param   bool    $remote    True if the file to check is a remote file
	 *
	 * @return  mixed  Mime type if found | False if no mime type is found.
	 *
	 * @since   3.0
	 */
	public function findMimeType($filename, $remote=false)
	{
		if ($remote || JFile::exists($filename))
		{
			if ($remote)
			{
				$dirPath   = dirname($filename);
				$imageName = rawurlencode(basename($filename));
				$filename  = $dirPath . '/' . $imageName;
			}

			$url_parts = @parse_url($filename);

			if (isset($url_parts['scheme']) && substr($url_parts['scheme'], 0, 3) == 'ftp')
			{
				$host = $url_parts['host'];

				if ($host)
				{
					$port = (isset($url_parts['port'])) ? $url_parts['port'] : 21;
					$user = $url_parts['user'];
					$pass = $url_parts['pass'];

					$ftp = JClientFtp::getInstance($host, $port, array(), $user, $pass);

					if ($ftp->read($url_parts['path'], $buffer))
					{
						$string = substr($buffer, 0, 20);
						$max_length_found = 0;

						foreach ($this->mimeTypes as $type)
						{
							if (stripos(bin2hex($string), $type['signature'], 0) !== false)
							{
								if (strlen($type['signature']) > $max_length_found)
								{
									$max_length_found = strlen($type['signature']);

									if (isset($type['mime_type']))
									{
										$this->_found_mime_type['mime_type'] = $type['mime_type'];

										return true;
									}
								}
							}
						}
					}
				}

				$read = false;
			}
			else
			{
				$handle = @fopen($filename, "r");

				if ($handle)
				{
					$string = fread($handle, 20);
					$this->log->add('Identity string: ' . bin2hex($string), false);
					$max_length_found = 0;

					foreach ($this->mimeTypes as $type)
					{
						if (stripos(bin2hex($string), $type['signature'], 0) !== false)
						{
							if (strlen($type['signature']) > $max_length_found)
							{
								$max_length_found = strlen($type['signature']);

								if (isset($type['mime_type']))
								{
									$this->_found_mime_type['mime_type'] = $type['mime_type'];
								}
							}
						}
					}

					fclose($handle);

					if (isset($this->_found_mime_type['mime_type']))
					{
						return $this->_found_mime_type['mime_type'];
					}
					else
					{
						return false;
					}
				}

				$read = false;
			}

			if (!$read)
			{
				// Cannot open the image file, do a simple check
				switch (strtolower(JFile::getExt($filename)))
				{
					case 'jpg':
					case 'jpeg':
						return 'image/jpeg';
						break;
					case 'png':
						return 'image/png';
						break;
					case 'gif':
						return 'image/gif';
						break;
					case 'bmp':
						return 'image/bmp';
						break;
					default:
						return false;
						break;
				}
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * List of known mime type signatures.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	private function LoadMimeTypes()
	{
		$this->mimeTypes[0]['signature'] = '474946383761';
		$this->mimeTypes[1]['signature'] = '424D';
		$this->mimeTypes[2]['signature'] = '4D5A';
		$this->mimeTypes[3]['signature'] = '504B0304';
		$this->mimeTypes[4]['signature'] = 'D0CF11E0A1B11AE1';
		$this->mimeTypes[5]['signature'] = '0100000058000000';
		$this->mimeTypes[6]['signature'] = '03000000C466C456';
		$this->mimeTypes[7]['signature'] = '3F5F0300';
		$this->mimeTypes[8]['signature'] = '1F8B08';
		$this->mimeTypes[9]['signature'] = '28546869732066696C65';
		$this->mimeTypes[10]['signature'] = '0000010000';
		$this->mimeTypes[11]['signature'] = '4C000000011402';
		$this->mimeTypes[12]['signature'] = '25504446';
		$this->mimeTypes[13]['signature'] = '5245474544495434';
		$this->mimeTypes[14]['signature'] = '7B5C727466';
		$this->mimeTypes[15]['signature'] = 'lh';
		$this->mimeTypes[16]['signature'] = 'MThd';
		$this->mimeTypes[17]['signature'] = '0A050108';
		$this->mimeTypes[18]['signature'] = '25215053';
		$this->mimeTypes[19]['signature'] = '2112';
		$this->mimeTypes[20]['signature'] = '1A02';
		$this->mimeTypes[21]['signature'] = '1A03';
		$this->mimeTypes[22]['signature'] = '1A04';
		$this->mimeTypes[23]['signature'] = '1A08';
		$this->mimeTypes[24]['signature'] = '1A09';
		$this->mimeTypes[25]['signature'] = '60EA';
		$this->mimeTypes[26]['signature'] = '41564920';
		$this->mimeTypes[27]['signature'] = '425A68';
		$this->mimeTypes[28]['signature'] = '49536328';
		$this->mimeTypes[29]['signature'] = '4C01';
		$this->mimeTypes[30]['signature'] = '303730373037';
		$this->mimeTypes[31]['signature'] = '4352555348';
		$this->mimeTypes[32]['signature'] = '3ADE68B1';
		$this->mimeTypes[33]['signature'] = '1F8B';
		$this->mimeTypes[34]['signature'] = '91334846';
		$this->mimeTypes[35]['signature'] = '3C68746D6C3E';
		$this->mimeTypes[36]['signature'] = '3C48544D4C3E';
		$this->mimeTypes[37]['signature'] = '3C21444F4354';
		$this->mimeTypes[38]['signature'] = '100';
		$this->mimeTypes[39]['signature'] = '5F27A889';
		$this->mimeTypes[40]['signature'] = '2D6C68352D';
		$this->mimeTypes[41]['signature'] = '20006040600';
		$this->mimeTypes[42]['signature'] = '00001A0007800100';
		$this->mimeTypes[43]['signature'] = '00001A0000100400';
		$this->mimeTypes[44]['signature'] = '20006800200';
		$this->mimeTypes[45]['signature'] = '00001A0002100400';
		$this->mimeTypes[46]['signature'] = '5B7665725D';
		$this->mimeTypes[47]['signature'] = '300000041505052';
		$this->mimeTypes[48]['signature'] = '1A0000030000';
		$this->mimeTypes[49]['signature'] = '4D47582069747064';
		$this->mimeTypes[50]['signature'] = '4D534346';
		$this->mimeTypes[51]['signature'] = '4D546864';
		$this->mimeTypes[52]['signature'] = '000001B3';
		$this->mimeTypes[53]['signature'] = '0902060000001000B9045C00';
		$this->mimeTypes[54]['signature'] = '0904060000001000F6055C00';
		$this->mimeTypes[55]['signature'] = '7FFE340A';
		$this->mimeTypes[56]['signature'] = '1234567890FF';
		$this->mimeTypes[57]['signature'] = '31BE000000AB0000';
		$this->mimeTypes[58]['signature'] = '1A00000300001100';
		$this->mimeTypes[59]['signature'] = '7E424B00';
		$this->mimeTypes[60]['signature'] = '504B0304';
		$this->mimeTypes[61]['signature'] = '89504E470D0A';
		$this->mimeTypes[62]['signature'] = '6D646174';
		$this->mimeTypes[63]['signature'] = '6D646174';
		$this->mimeTypes[64]['signature'] = '52617221';
		$this->mimeTypes[65]['signature'] = '2E7261FD';
		$this->mimeTypes[66]['signature'] = 'EDABEEDB';
		$this->mimeTypes[67]['signature'] = '2E736E64';
		$this->mimeTypes[68]['signature'] = '53495421';
		$this->mimeTypes[69]['signature'] = '53747566664974';
		$this->mimeTypes[70]['signature'] = '1F9D';
		$this->mimeTypes[71]['signature'] = '49492A';
		$this->mimeTypes[72]['signature'] = '4D4D2A';
		$this->mimeTypes[73]['signature'] = '554641';
		$this->mimeTypes[74]['signature'] = '57415645666D74';
		$this->mimeTypes[75]['signature'] = 'D7CDC69A';
		$this->mimeTypes[76]['signature'] = '4C000000';
		$this->mimeTypes[77]['signature'] = '504B3030504B0304';
		$this->mimeTypes[78]['signature'] = 'FF575047';
		$this->mimeTypes[79]['signature'] = 'FF575043';
		$this->mimeTypes[80]['signature'] = '3C3F786D6C';
		$this->mimeTypes[81]['signature'] = 'FFFE3C0052004F004F0054005300540055004200';
		$this->mimeTypes[82]['signature'] = '3C21454E54495459';
		$this->mimeTypes[83]['signature'] = '5A4F4F20';
		$this->mimeTypes[84]['signature'] = 'FFD8FFFE';
		$this->mimeTypes[85]['signature'] = 'FFD8FFE0';
		$this->mimeTypes[86]['signature'] = 'FFD8FFEE';
		$this->mimeTypes[87]['signature'] = 'FFD8FFE1';
		$this->mimeTypes[88]['signature'] = 'FFD8FFE2';
		$this->mimeTypes[89]['signature'] = 'FFD8FFDB';
		$this->mimeTypes[90]['signature'] = '474946383961';

		// Extensions
		$this->mimeTypes[0]['extension'] = '.gif';
		$this->mimeTypes[1]['extension'] = '.bmp';
		$this->mimeTypes[2]['extension'] = '.exe;.com;.386;.ax;.acm;.sys;.dll;.drv;.flt;.fon;.ocx;.scr;.lrc;.vxd;.cpl;.x32';
		$this->mimeTypes[3]['extension'] = '.zip';
		$this->mimeTypes[4]['extension'] = '.doc;.xls;.xlt;.ppt;.apr';
		$this->mimeTypes[5]['extension'] = '.emf';
		$this->mimeTypes[6]['extension'] = '.evt';
		$this->mimeTypes[7]['extension'] = '.gid;.hlp;.lhp';
		$this->mimeTypes[8]['extension'] = '.gz';
		$this->mimeTypes[9]['extension'] = '.hqx';
		$this->mimeTypes[10]['extension'] = '.ico';
		$this->mimeTypes[11]['extension'] = '.lnk';
		$this->mimeTypes[12]['extension'] = '.pdf';
		$this->mimeTypes[13]['extension'] = '.reg';
		$this->mimeTypes[14]['extension'] = '.rtf';
		$this->mimeTypes[15]['extension'] = '.lzh';
		$this->mimeTypes[16]['extension'] = '.mid';
		$this->mimeTypes[17]['extension'] = '.pcx';
		$this->mimeTypes[18]['extension'] = '.eps';
		$this->mimeTypes[19]['extension'] = '.ain';
		$this->mimeTypes[20]['extension'] = '.arc';
		$this->mimeTypes[21]['extension'] = '.arc';
		$this->mimeTypes[22]['extension'] = '.arc';
		$this->mimeTypes[23]['extension'] = '.arc';
		$this->mimeTypes[24]['extension'] = '.arc';
		$this->mimeTypes[25]['extension'] = '.arj';
		$this->mimeTypes[26]['extension'] = '.avi';
		$this->mimeTypes[27]['extension'] = '.bz;.bz2';
		$this->mimeTypes[28]['extension'] = '.cab';
		$this->mimeTypes[29]['extension'] = '.obj';
		$this->mimeTypes[30]['extension'] = '.tar;.cpio';
		$this->mimeTypes[31]['extension'] = '.cru;.crush';
		$this->mimeTypes[32]['extension'] = '.dcx';
		$this->mimeTypes[33]['extension'] = '.gz;.tar;.tgz';
		$this->mimeTypes[34]['extension'] = '.hap';
		$this->mimeTypes[35]['extension'] = '.htm;.html';
		$this->mimeTypes[36]['extension'] = '.htm;.html';
		$this->mimeTypes[37]['extension'] = '.htm;.html';
		$this->mimeTypes[38]['extension'] = '.ico';
		$this->mimeTypes[39]['extension'] = '.jar';
		$this->mimeTypes[40]['extension'] = '.lha';
		$this->mimeTypes[41]['extension'] = '.wk1;.wks';
		$this->mimeTypes[42]['extension'] = '.fm3';
		$this->mimeTypes[43]['extension'] = '.wk3';
		$this->mimeTypes[44]['extension'] = '.fmt';
		$this->mimeTypes[45]['extension'] = '.wk4';
		$this->mimeTypes[46]['extension'] = '.ami';
		$this->mimeTypes[47]['extension'] = '.adx';
		$this->mimeTypes[48]['extension'] = '.nsf;.ntf';
		$this->mimeTypes[49]['extension'] = '.ds4';
		$this->mimeTypes[50]['extension'] = '.cab';
		$this->mimeTypes[51]['extension'] = '.mid';
		$this->mimeTypes[52]['extension'] = '.mpg;.mpeg';
		$this->mimeTypes[53]['extension'] = '.xls';
		$this->mimeTypes[54]['extension'] = '.xls';
		$this->mimeTypes[55]['extension'] = '.doc';
		$this->mimeTypes[56]['extension'] = '.doc';
		$this->mimeTypes[57]['extension'] = '.doc';
		$this->mimeTypes[58]['extension'] = '.nsf';
		$this->mimeTypes[59]['extension'] = '.psp';
		$this->mimeTypes[60]['extension'] = '.zip';
		$this->mimeTypes[61]['extension'] = '.png';
		$this->mimeTypes[62]['extension'] = '.mov';
		$this->mimeTypes[63]['extension'] = '.qt';
		$this->mimeTypes[64]['extension'] = '.rar';
		$this->mimeTypes[65]['extension'] = '.ra;.ram';
		$this->mimeTypes[66]['extension'] = '.rpm';
		$this->mimeTypes[67]['extension'] = '.au';
		$this->mimeTypes[68]['extension'] = '.sit';
		$this->mimeTypes[69]['extension'] = '.sit';
		$this->mimeTypes[70]['extension'] = '.z';
		$this->mimeTypes[71]['extension'] = '.tif;.tiff';
		$this->mimeTypes[72]['extension'] = '.tif;.tiff';
		$this->mimeTypes[73]['extension'] = '.ufa';
		$this->mimeTypes[74]['extension'] = '.wav';
		$this->mimeTypes[75]['extension'] = '.wmf';
		$this->mimeTypes[76]['extension'] = '.lnk';
		$this->mimeTypes[77]['extension'] = '.zip';
		$this->mimeTypes[78]['extension'] = '.wpg';
		$this->mimeTypes[79]['extension'] = '.wp';
		$this->mimeTypes[80]['extension'] = '.xml';
		$this->mimeTypes[81]['extension'] = '.xml';
		$this->mimeTypes[82]['extension'] = '.dtd';
		$this->mimeTypes[83]['extension'] = '.zoo';
		$this->mimeTypes[84]['extension'] = '.jpeg;.jpe;.jpg';
		$this->mimeTypes[85]['extension'] = '.jpeg;.jpe;.jpg';
		$this->mimeTypes[86]['extension'] = '.jpeg;.jpe;.jpg';
		$this->mimeTypes[87]['extension'] = '.jpeg;.jpe;.jpg';
		$this->mimeTypes[88]['extension'] = '.jpeg;.jpe;.jpg';
		$this->mimeTypes[89]['extension'] = '.jpeg;.jpe;.jpg';
		$this->mimeTypes[90]['extension'] = '.gif';

		// Descriptions
		$this->mimeTypes[0]['description'] = 'GIF 87A';
		$this->mimeTypes[1]['description'] = 'Windows Bitmap';
		$this->mimeTypes[2]['description'] = 'Executable File ';
		$this->mimeTypes[3]['description'] = 'Zip Compressed';
		$this->mimeTypes[4]['description'] = 'MS Compound Document v1 or Lotus Approach APR file';
		$this->mimeTypes[5]['description'] = 'xtended (Enhanced) Windows Metafile Format';
		$this->mimeTypes[6]['description'] = 'Windows NT/2000 Event Viewer Log File';
		$this->mimeTypes[7]['description'] = 'Windows Help File';
		$this->mimeTypes[8]['description'] = 'GZ Compressed File';
		$this->mimeTypes[9]['description'] = 'Macintosh BinHex 4 Compressed Archive';
		$this->mimeTypes[10]['description'] = 'Icon File';
		$this->mimeTypes[11]['description'] = 'Windows Link File';
		$this->mimeTypes[12]['description'] = 'Adobe PDF File';
		$this->mimeTypes[13]['description'] = 'Registry Data File';
		$this->mimeTypes[14]['description'] = 'Rich Text Format File';
		$this->mimeTypes[15]['description'] = 'Lzh compression file';
		$this->mimeTypes[16]['description'] = 'Musical Instrument Digital Interface MIDI-sequention Sound';
		$this->mimeTypes[17]['description'] = 'PC Paintbrush Bitmap Graphic';
		$this->mimeTypes[18]['description'] = 'Adobe EPS File';
		$this->mimeTypes[19]['description'] = 'AIN Archive File';
		$this->mimeTypes[20]['description'] = 'ARC/PKPAK Compressed 1';
		$this->mimeTypes[21]['description'] = 'ARC/PKPAK Compressed 2';
		$this->mimeTypes[22]['description'] = 'ARC/PKPAK Compressed 3';
		$this->mimeTypes[23]['description'] = 'ARC/PKPAK Compressed 4';
		$this->mimeTypes[24]['description'] = 'ARC/PKPAK Compressed 5';
		$this->mimeTypes[25]['description'] = 'ARJ Compressed';
		$this->mimeTypes[26]['description'] = 'Audio Video Interleave (AVI)';
		$this->mimeTypes[27]['description'] = 'Bzip Archive';
		$this->mimeTypes[28]['description'] = 'Cabinet File';
		$this->mimeTypes[29]['description'] = 'Compiled Object Module';
		$this->mimeTypes[30]['description'] = 'CPIO Archive File';
		$this->mimeTypes[31]['description'] = 'CRUSH Archive File';
		$this->mimeTypes[32]['description'] = 'DCX Graphic File';
		$this->mimeTypes[33]['description'] = 'Gzip Archive File';
		$this->mimeTypes[34]['description'] = 'HAP Archive File';
		$this->mimeTypes[35]['description'] = 'HyperText Markup Language 1';
		$this->mimeTypes[36]['description'] = 'HyperText Markup Language 2';
		$this->mimeTypes[37]['description'] = 'HyperText Markup Language 3';
		$this->mimeTypes[38]['description'] = 'ICON File';
		$this->mimeTypes[39]['description'] = 'JAR Archive File';
		$this->mimeTypes[40]['description'] = 'LHA Compressed';
		$this->mimeTypes[41]['description'] = 'Lotus 123 v1 Worksheet';
		$this->mimeTypes[42]['description'] = 'Lotus 123 v3 FMT file';
		$this->mimeTypes[43]['description'] = 'Lotus 123 v3 Worksheet';
		$this->mimeTypes[44]['description'] = 'Lotus 123 v4 FMT file';
		$this->mimeTypes[45]['description'] = 'Lotus 123 v5';
		$this->mimeTypes[46]['description'] = 'Lotus Ami Pro';
		$this->mimeTypes[47]['description'] = 'Lotus Approach ADX file';
		$this->mimeTypes[48]['description'] = 'Lotus Notes Database/Template';
		$this->mimeTypes[49]['description'] = 'Micrografix Designer 4';
		$this->mimeTypes[50]['description'] = 'Microsoft CAB File Format';
		$this->mimeTypes[51]['description'] = 'Midi Audio File';
		$this->mimeTypes[52]['description'] = 'MPEG Movie';
		$this->mimeTypes[53]['description'] = 'MS Excel v2';
		$this->mimeTypes[54]['description'] = 'MS Excel v4';
		$this->mimeTypes[55]['description'] = 'MS Word';
		$this->mimeTypes[56]['description'] = 'MS Word 6.0';
		$this->mimeTypes[57]['description'] = 'MS Word for DOS 6.0';
		$this->mimeTypes[58]['description'] = 'Notes Database';
		$this->mimeTypes[59]['description'] = 'PaintShop Pro Image File';
		$this->mimeTypes[60]['description'] = 'PKZIP Compressed';
		$this->mimeTypes[61]['description'] = 'PNG Image File';
		$this->mimeTypes[62]['description'] = 'QuickTime Movie';
		$this->mimeTypes[63]['description'] = 'Quicktime Movie File';
		$this->mimeTypes[64]['description'] = 'RAR Archive File';
		$this->mimeTypes[65]['description'] = 'Real Audio File';
		$this->mimeTypes[66]['description'] = 'RPM Archive File';
		$this->mimeTypes[67]['description'] = 'SoundMachine Audio File';
		$this->mimeTypes[68]['description'] = 'Stuffit v1 Archive File';
		$this->mimeTypes[69]['description'] = 'Stuffit v5 Archive File';
		$this->mimeTypes[70]['description'] = 'TAR Compressed Archive File';
		$this->mimeTypes[71]['description'] = 'TIFF (Intel)';
		$this->mimeTypes[72]['description'] = 'TIFF (Motorola)';
		$this->mimeTypes[73]['description'] = 'UFA Archive File';
		$this->mimeTypes[74]['description'] = 'Wave Files';
		$this->mimeTypes[75]['description'] = 'Windows Meta File';
		$this->mimeTypes[76]['description'] = 'Windows Shortcut (Link File)';
		$this->mimeTypes[77]['description'] = 'WINZIP Compressed';
		$this->mimeTypes[78]['description'] = 'WordPerfect Graphics';
		$this->mimeTypes[79]['description'] = 'WordPerfect v5 or v6';
		$this->mimeTypes[80]['description'] = 'XML Document';
		$this->mimeTypes[81]['description'] = 'XML Document (ROOTSTUB)';
		$this->mimeTypes[82]['description'] = 'XML DTD';
		$this->mimeTypes[83]['description'] = 'ZOO Archive File';
		$this->mimeTypes[84]['description'] = 'JPG Graphic File';
		$this->mimeTypes[85]['description'] = 'JPG Graphic File';
		$this->mimeTypes[86]['description'] = 'JPG Graphic File';
		$this->mimeTypes[87]['description'] = 'JPG Graphic File';
		$this->mimeTypes[88]['description'] = 'JPG Graphic File';
		$this->mimeTypes[89]['description'] = 'JPG Graphic File';
		$this->mimeTypes[90]['description'] = 'GIF 89A';

		// Mime descriptions
		$this->mimeTypes[0]['mime_type'] = 'image/gif';
		$this->mimeTypes[1]['mime_type'] = 'image/bmp';
		$this->mimeTypes[2]['mime_type'] = '';
		$this->mimeTypes[3]['mime_type'] = '';
		$this->mimeTypes[4]['mime_type'] = '';
		$this->mimeTypes[5]['mime_type'] = '';
		$this->mimeTypes[6]['mime_type'] = '';
		$this->mimeTypes[7]['mime_type'] = '';
		$this->mimeTypes[8]['mime_type'] = '';
		$this->mimeTypes[9]['mime_type'] = '';
		$this->mimeTypes[10]['mime_type'] = '';
		$this->mimeTypes[11]['mime_type'] = '';
		$this->mimeTypes[12]['mime_type'] = 'application/pdf';
		$this->mimeTypes[13]['mime_type'] = '';
		$this->mimeTypes[14]['mime_type'] = '';
		$this->mimeTypes[15]['mime_type'] = '';
		$this->mimeTypes[16]['mime_type'] = '';
		$this->mimeTypes[17]['mime_type'] = '';
		$this->mimeTypes[18]['mime_type'] = '';
		$this->mimeTypes[19]['mime_type'] = '';
		$this->mimeTypes[20]['mime_type'] = '';
		$this->mimeTypes[21]['mime_type'] = '';
		$this->mimeTypes[22]['mime_type'] = '';
		$this->mimeTypes[23]['mime_type'] = '';
		$this->mimeTypes[24]['mime_type'] = '';
		$this->mimeTypes[25]['mime_type'] = '';
		$this->mimeTypes[26]['mime_type'] = '';
		$this->mimeTypes[27]['mime_type'] = '';
		$this->mimeTypes[28]['mime_type'] = '';
		$this->mimeTypes[29]['mime_type'] = '';
		$this->mimeTypes[30]['mime_type'] = '';
		$this->mimeTypes[31]['mime_type'] = '';
		$this->mimeTypes[32]['mime_type'] = '';
		$this->mimeTypes[33]['mime_type'] = '';
		$this->mimeTypes[34]['mime_type'] = '';
		$this->mimeTypes[35]['mime_type'] = '';
		$this->mimeTypes[36]['mime_type'] = '';
		$this->mimeTypes[37]['mime_type'] = '';
		$this->mimeTypes[38]['mime_type'] = '';
		$this->mimeTypes[39]['mime_type'] = '';
		$this->mimeTypes[40]['mime_type'] = '';
		$this->mimeTypes[41]['mime_type'] = '';
		$this->mimeTypes[42]['mime_type'] = '';
		$this->mimeTypes[43]['mime_type'] = '';
		$this->mimeTypes[44]['mime_type'] = '';
		$this->mimeTypes[45]['mime_type'] = '';
		$this->mimeTypes[46]['mime_type'] = '';
		$this->mimeTypes[47]['mime_type'] = '';
		$this->mimeTypes[48]['mime_type'] = '';
		$this->mimeTypes[49]['mime_type'] = '';
		$this->mimeTypes[50]['mime_type'] = '';
		$this->mimeTypes[51]['mime_type'] = '';
		$this->mimeTypes[52]['mime_type'] = '';
		$this->mimeTypes[53]['mime_type'] = '';
		$this->mimeTypes[54]['mime_type'] = '';
		$this->mimeTypes[55]['mime_type'] = '';
		$this->mimeTypes[56]['mime_type'] = '';
		$this->mimeTypes[57]['mime_type'] = '';
		$this->mimeTypes[58]['mime_type'] = '';
		$this->mimeTypes[59]['mime_type'] = '';
		$this->mimeTypes[60]['mime_type'] = '';
		$this->mimeTypes[61]['mime_type'] = 'image/png';
		$this->mimeTypes[62]['mime_type'] = '';
		$this->mimeTypes[63]['mime_type'] = '';
		$this->mimeTypes[64]['mime_type'] = '';
		$this->mimeTypes[65]['mime_type'] = '';
		$this->mimeTypes[66]['mime_type'] = '';
		$this->mimeTypes[67]['mime_type'] = '';
		$this->mimeTypes[68]['mime_type'] = '';
		$this->mimeTypes[69]['mime_type'] = '';
		$this->mimeTypes[70]['mime_type'] = '';
		$this->mimeTypes[71]['mime_type'] = '';
		$this->mimeTypes[72]['mime_type'] = '';
		$this->mimeTypes[73]['mime_type'] = '';
		$this->mimeTypes[74]['mime_type'] = '';
		$this->mimeTypes[75]['mime_type'] = '';
		$this->mimeTypes[76]['mime_type'] = '';
		$this->mimeTypes[77]['mime_type'] = '';
		$this->mimeTypes[78]['mime_type'] = '';
		$this->mimeTypes[79]['mime_type'] = '';
		$this->mimeTypes[80]['mime_type'] = '';
		$this->mimeTypes[81]['mime_type'] = '';
		$this->mimeTypes[82]['mime_type'] = '';
		$this->mimeTypes[83]['mime_type'] = '';
		$this->mimeTypes[84]['mime_type'] = 'image/jpeg';
		$this->mimeTypes[85]['mime_type'] = 'image/jpeg';
		$this->mimeTypes[86]['mime_type'] = 'image/jpeg';
		$this->mimeTypes[87]['mime_type'] = 'image/jpeg';
		$this->mimeTypes[88]['mime_type'] = 'image/jpeg';
		$this->mimeTypes[89]['mime_type'] = 'image/jpeg';
		$this->mimeTypes[90]['mime_type'] = 'image/gif';
	}

	/**
	 * List of known image types.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	private function loadImageTypes()
	{
		$this->imageTypes[0]['mime_type'] = 'image/gif';
		$this->imageTypes[1]['mime_type'] = 'image/bmp';
		$this->imageTypes[2]['mime_type'] = 'image/png';
		$this->imageTypes[3]['mime_type'] = 'image/jpeg';
		$this->imageTypes[4]['mime_type'] = 'image/jpeg';
		$this->imageTypes[5]['mime_type'] = 'image/gif';
	}

	/**
	 * Convert/Resize an image.
	 *
	 * @param   array  $file_details  Contains all the variables for creating a new image
	 *
	 * @return  mixed  Filename of created file if file has been created | false if file has not been created.
	 *
	 * @since   3.0
	 */
	public function convertImage($file_details)
	{
		// Set all details
		foreach ($file_details as $type => $value)
		{
			switch ($type)
			{
				case 'maxsize':
					if ($value)
					{
						$this->maxSize = true;
					}
					else
					{
						$this->maxSize = false;
					}
					break;
				case 'bgred':
					if ($file_details['bgred'] >= 0 || $file_details['bgred'] <= 255)
					{
						$this->bg_red = $file_details['bgred'];
					}
					else
					{
						$this->bg_red = 0;
					}
					break;
				case 'bggreen':
					if ($file_details['bggreen'] >= 0 || $file_details['bggreen'] <= 255)
					{
						$this->bg_green = $file_details['bggreen'];
					}
					else
					{
						$this->bg_green = 0;
					}
					break;
				case 'bgblue':
					if ($file_details['bgblue'] >= 0 || $file_details['bgblue'] <= 255)
					{
						$this->bg_blue = $file_details['bgblue'];
					}
					else
					{
						$this->bg_blue = 0;
					}
					break;
				default:
					$this->$type = $value;
					break;
			}
		}

		if ($this->newImgCreate())
		{
			return $this->file_out;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Create a new image.
	 *
	 * @return  boolean  True on success | False on failure.
	 *
	 * @since   3.0
	 */
	private function newImgCreate()
	{
		// Clear the cache
		clearstatcache();

		switch (strtolower($this->mime_type))
		{
			case 'image/gif':
				if (function_exists('imagecreatefromgif'))
				{
					$orig_img = @imagecreatefromgif($this->file);
				}
				else
				{
					return false;
				}
				break;
			case 'image/jpg':
			case 'image/jpeg':
				if (function_exists('imagecreatefromjpeg'))
				{
					$orig_img = @imagecreatefromjpeg($this->file);
				}
				else
				{
					return false;
				}
				break;
			case 'image/png':
				if (function_exists('imagecreatefrompng'))
				{
					$orig_img = @imagecreatefrompng($this->file);
				}
				else
				{
					return false;
				}
				break;
			default:
				return false;
				break;
		}

		if ($orig_img)
		{
			$this->log->add('Save the new image', false);

			// Save the new image
			$img_resize = $this->NewImgSave($this->NewImgResize($orig_img));

			// Clean up old image
			ImageDestroy($orig_img);
		}
		else
		{
			$this->log->add('Cannot_read_original_image', false);
			$img_resize = false;
		}

		if ($img_resize)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Resize the image.
	 *
	 * Includes function ImageCreateTrueColor and ImageCopyResampled which are available only under GD 2.0.1 or higher !
	 *
	 * @return  mixed  Image handler on success | False on failure.
	 *
	 * @since   3.0
	 */
	private function NewImgResize($orig_img)
	{
		$orig_size = getimagesize($this->file);

		$maxX = $this->file_out_width;
		$maxY = $this->file_out_height;

		if ($orig_size[0] < $orig_size[1])
		{
			$this->file_out_width = $this->file_out_height * ($orig_size[0] / $orig_size[1]);
			$adjustX = ($maxX - $this->file_out_width) / 2;
			$adjustY = 0;
		}
		else
		{
			$this->file_out_height = $this->file_out_width / ($orig_size[0] / $orig_size[1]);
			$adjustX = 0;
			$adjustY = ($maxY - $this->file_out_height) / 2;
		}

		while ($this->file_out_width < 1 || $this->file_out_height < 1)
		{
			$this->file_out_width *= 2;
			$this->file_out_height *= 2;
		}

		// See if we need to create an image at maximum size
		if ($this->maxSize)
		{
			if (function_exists("imagecreatetruecolor"))
			{
				$im_out = imagecreatetruecolor($maxX, $maxY);
			}
			else
			{
				$im_out = imagecreate($maxX, $maxY);
			}

			if ($im_out)
			{
				// Need to image fill just in case image is transparent, don't always want black background
				$bgfill = imagecolorallocate($im_out, $this->bg_red, $this->bg_green, $this->bg_blue);

				if (function_exists("imageAntiAlias"))
				{
					imageAntiAlias($im_out, true);
				}

				imagealphablending($im_out, false);

				if (function_exists("imagesavealpha"))
				{
					imagesavealpha($im_out, true);
				}

				if (function_exists("imagecolorallocatealpha"))
				{
					$transparent = imagecolorallocatealpha($im_out, 255, 255, 255, 127);
				}

				if (function_exists("imagecopyresampled"))
				{
					ImageCopyResampled($im_out, $orig_img, $adjustX, $adjustY, 0, 0, $this->file_out_width, $this->file_out_height, $orig_size[0], $orig_size[1]);
				}
				else
				{
					ImageCopyResized($im_out, $orig_img, $adjustX, $adjustY, 0, 0, $this->file_out_width, $this->file_out_height, $orig_size[0], $orig_size[1]);
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			if (function_exists("imagecreatetruecolor"))
			{
				$im_out = ImageCreateTrueColor($this->file_out_width, $this->file_out_height);
			}
			else
			{
				$im_out = imagecreate($this->file_out_width, $this->file_out_height);
			}

			if ($im_out)
			{
				if (function_exists("imageAntiAlias"))
				{
					imageAntiAlias($im_out, true);
				}

				imagealphablending($im_out, false);

				if (function_exists("imagesavealpha"))
				{
					imagesavealpha($im_out, true);
				}

				if (function_exists("imagecolorallocatealpha"))
				{
					$transparent = imagecolorallocatealpha($im_out, 255, 255, 255, 127);
				}

				if (function_exists("imagecopyresampled"))
				{
					ImageCopyResampled($im_out, $orig_img, 0, 0, 0, 0, $this->file_out_width, $this->file_out_height, $orig_size[0], $orig_size[1]);
				}
				else
				{
					ImageCopyResized($im_out, $orig_img, 0, 0, 0, 0, $this->file_out_width, $this->file_out_height, $orig_size[0], $orig_size[1]);
				}
			}
			else
			{
				return false;
			}
		}

		return $im_out;
	}

	/**
	 * Save the new image.
	 *
	 * @param   object  $new_img  The image handler
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   3.0
	 */
	private function NewImgSave($new_img)
	{
		// Lets see if we need to rename the output file since we know the sizes
		$this->log->add('File out extension: ' . $this->file_out_extension, false);
		$this->log->add('File out: ' . $this->file_out, false);

		switch (strtolower($this->file_out_extension))
		{
			case 'gif':
				if (strtolower(substr($this->file_out, strlen($this->file_out) - 4, 4)) !== '.gif')
				{
					$this->file_out .= '.gif';
				}

				return imagegif($new_img, $this->file_out);
				break;
			case 'jpg':
				if (strtolower(substr($this->file_out, strlen($this->file_out) - 4, 4)) !== '.jpg')
				{
					$this->file_out .= '.jpg';
				}

				return imagejpeg($new_img, $this->file_out, 100);
				break;
			case 'jpeg':
				if (strtolower(substr($this->file_out, strlen($this->file_out) - 5, 5)) !== '.jpeg')
				{
					$this->file_out .= '.jpeg';
				}

				return imagejpeg($new_img, $this->file_out, 100);
				break;
			case 'png':
				if (strtolower(substr($this->file_out, strlen($this->file_out) - 4, 4)) !== '.png')
				{
					$this->file_out .= '.png';
				}

				return imagepng($new_img, $this->file_out);
				break;
			default:
				$this->log->add('No matching extension found', false);

				return false;
				break;
		}
	}

	/**
	 * Process an image.
	 *
	 * @param   string  $name         Full path and name of the image
	 * @param   string  $output_path  The destination location of the image including trailing /
	 * @param   string  $output_name  Name of the output image
	 *
	 * @return  array  An array with image data.
	 *
	 * @since   3.0
	 */
	public function processImage($name, $output_path, $output_name=null)
	{
		// Cleanup
		$base = JPath::clean(JPATH_SITE, '/');

		if (!empty($output_path))
		{
			$output_path = JPath::clean($output_path, '/');
		}

		$this->_imagedata = array();
		$this->_imagedata['base'] = $base;

		if ($this->isRemote($name))
		{
			$this->_imagedata['name'] = $name;
			$this->_imagedata['isremote'] = true;
		}
		else
		{
			$this->_imagedata['name'] = $base . '/' . JPath::clean($name, '/');
			$this->_imagedata['isremote'] = false;
		}

		$this->_imagedata['output_path'] = $output_path;
		$this->_imagedata['output_name'] = (empty($output_name)) ? basename($name) : $output_name;
		$this->_imagedata['extension'] = JFile::getExt($name);
		$this->_imagedata['exists'] = false;
		$this->_imagedata['isimage'] = false;
		$this->_imagedata['mime_type'] = null;

		// See if we need to handle a remote file
		if ($this->_imagedata['isremote'])
		{
			$this->log->add('Process remote file: ' . $this->_imagedata['name'], false);

			$imageUsername  = $this->template->get('image_username', '');
			$imagePassword  = $this->template->get('image_password', '');
			$method         = $this->template->get('image_method', 'POST');
			$credentialType = $this->template->get('image_credential_type', 'htaccess');

			if ($imageUsername && $imagePassword)
			{
				$remoteMethod = $this->csvihelper->fileExistsRemote(
					$this->_imagedata['name'], $imageUsername, $imagePassword, $method, 'user', 'password', $credentialType
				);
			}
			else
			{
				$remoteMethod = $this->csvihelper->fileExistsRemote($this->_imagedata['name']);
			}

			if ($remoteMethod)
			{
				$this->_imagedata['exists'] = true;

				// Check if this is an image or not
				if ($this->isImage($this->_imagedata['name'], true))
				{
					$this->_imagedata['isimage'] = true;
				}
			}
			else
			{
				$this->log->add('Remote file does not exist: ' . $this->_imagedata['name'], false);
				$this->_imagedata['exists'] = false;
			}
		}
		elseif (JFile::exists($this->_imagedata['name']))
		{
			$this->log->add('Process file: ' . $this->_imagedata['name'], false);
			$this->_imagedata['exists'] = true;

			// Check if this is an image or not
			if ($this->isImage($this->_imagedata['name']))
			{
				$this->_imagedata['isimage'] = true;
			}
		}
		else
		{
			// File does not exist
			$this->log->add(JText::sprintf('COM_CSVI_DEBUG_FILE_NOT_FOUND', $this->_imagedata['name']), false);
			$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_DEBUG_FILE_NOT_FOUND', $this->_imagedata['name']));

			return $this->_imagedata;
		}

		// Process if it is an image
		if ($this->_imagedata['isimage'])
		{
			$this->log->add('Process image file', false);

			// Clean up the images first
			$this->cleanupImage();

			// Convert the full image
			if ($this->_imagedata['convert'])
			{
				$this->convertFullImage();
			}

			// Save the remote images on the server
			if ($this->_imagedata['isremote'] && $this->template->get('save_images_on_server', 'image') && !$this->_imagedata['convert'])
			{
				// Sanitize filename
				$this->_imagedata['output_name'] = $this->cleanFilename($this->_imagedata['output_name']);
				$from = $this->_imagedata['name'];
				$to = $this->_imagedata['base'] . '/' . $this->_imagedata['output_path'] . $this->_imagedata['output_name'];

				// Check if the local file should be deleted
				if (JFile::exists($to) && !$this->template->get('redownload_external_image', 'image'))
				{
					// Do not delete if the files are the same, otherwise the original image gets deleted. This is because
					// the remote image is set to a local image in cleanupImage()
					if ($from !== $to)
					{
						JFile::delete($from);
					}
				}
				else
				{
					if ($from !== $to)
					{
						JFile::delete($to);
						$this->log->add('Store remote file on server ' . $from . ' --> ' . $to, false);
						JFile::move($from, $to);
					}
				}
			}
			elseif ($this->_imagedata['isremote'])
			{
				// Remove temporary file
				JFile::delete($this->_imagedata['name']);
			}

			// Check if any images need to be renamed
			$this->renameImage();

			// Check if the full image needs to be resized
			$this->resizeFullImage();

			// Convert images
			$this->imageTypeCheck();
		}
		else
		{
			if ($this->_imagedata['exists'])
			{
				$this->log->add('The file ' . $name . ' is not an image', false);

				// Set the extension to the original extension
				$this->_imagedata['output_name'] = JFile::stripExt($this->_imagedata['output_name']) . '.' . $this->_imagedata['extension'];

				// Get more details
				$this->collectFileDetails();
			}
		}

		return $this->_imagedata;
	}

	/**
	 * Check if a file is a remote file or not
	 *
	 * @param   string  $path  The full path to check
	 *
	 * Remote images can be located on an HTTP location or an FTP location.
	 *
	 * @return  bool  True if file is remote | False if file is not remote.
	 *
	 * @since   3.0
	 */
	public function isRemote($path)
	{
		if (substr(strtolower($path), 0, 4) == 'http')
		{
			return true;
		}
		elseif (substr(strtolower($path), 0, 3) == 'ftp')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Collect file details for non-image files.
	 *
	 * @return  void.
	 *
	 * @since   2.3.10
	 */
	public function collectFileDetails()
	{
		$this->_imagedata['mime_type'] = $this->findMimeType($this->_imagedata['name']);
		$this->_imagedata['isimage'] = 0;

		// Clean up the images first
		$this->cleanupImage();

		// Save the remote images on the server
		if ($this->_imagedata['isremote'] && $this->template->get('save_images_on_server', 'image'))
		{
			// Sanitize filename
			$this->_imagedata['output_name'] = $this->cleanFilename($this->_imagedata['output_name']);
			$from = $this->_imagedata['name'];
			$to = $this->_imagedata['base'] . '/' . $this->_imagedata['output_path'] . $this->_imagedata['output_name'];

			// Check if the local file should be deleted
			if (JFile::exists($to) && !$this->template->get('redownload_external_image', 'image'))
			{
				JFile::delete($from);
			}
			else
			{
				JFile::delete($to);
				$this->log->add('Store remote file on server ' . $from . ' --> ' . $to, false);
				JFile::move($from, $to);
			}
		}
	}

	/**
	 * Create a thumbnail image.
	 *
	 * @param   string  $original     The full path and name of the large image
	 * @param   string  $output_path  The path to store the thumbnail
	 * @param   string  $output_name  The name of the thumbnail
	 *
	 * @return  mixed  Full thumbnail path and name on success | False on failure.
	 *
	 * @since   4.0
	 */
	public function createThumbnail($original, $output_path, $output_name)
	{
		$base = JPath::clean(JPATH_SITE, '/');

		// Make sure the thumbnail is the same file type as the full image
		if ($this->template->get('thumb_check_filetype', 'image') && JFile::getExt($original) != JFile::getExt($output_name))
		{
			$output_name = JFile::stripExt($output_name) . '.' . JFile::getExt($original);
		}

		// Clean up the output name
		$output_name = $this->setCase($output_name);

		// Check if the original is an external image
		if (!$this->isRemote($original))
		{
			$original = $base . '/' . $original;
			$file_exists = JFile::exists($original);
			$remote = false;
		}
		else
		{
			$imageUsername  = $this->template->get('image_username', '');
			$imagePassword  = $this->template->get('image_password', '');
			$method         = $this->template->get('image_method', 'POST');
			$credentialType = $this->template->get('image_credential_type', 'htaccess');

			if ($imageUsername && $imagePassword)
			{
				$file_exists = $this->csvihelper->fileExistsRemote(
					$original, $imageUsername, $imagePassword, $method, 'user', 'password', $credentialType
				);
			}
			else
			{
				$file_exists = $this->csvihelper->fileExistsRemote($original);
			}

			$remote = true;
		}

		// Check if thumbsize is greater than 0
		if ($this->template->get('thumb_width') >= 1 && $this->template->get('thumb_height') >= 1)
		{
			// Check if the image folders exists
			$thumb_folder = JPATH_SITE . '/' . $output_path . dirname($output_name);

			if (!JFolder::exists($thumb_folder))
			{
				$this->log->add('Create thumbnail folder: ' . $thumb_folder, false);
				JFolder::create($thumb_folder);
			}

			// Check if the target thumb exists, if yes delete it
			if (JFile::exists($base . '/' . $output_path . $output_name))
			{
				JFile::delete($base . '/' . $output_path . $output_name);
			}

			// Check if the original file exists
			$this->log->add('Check original file: ' . $original, false);

			if ($file_exists)
			{
				// Collect all thumbnail details
				$thumb_file_details = array();
				$thumb_file_details['file'] = $original;
				$thumb_file_details['file_extension'] = JFile::getExt($original);
				$thumb_file_details['file_out'] = $base . '/' . $output_path . $output_name;
				$thumb_file_details['maxsize'] = 0;
				$thumb_file_details['bgred'] = 255;
				$thumb_file_details['bggreen'] = 255;
				$thumb_file_details['bgblue'] = 255;
				$thumb_file_details['file_out_width'] = $this->template->get('thumb_width', 'image');
				$thumb_file_details['file_out_height'] = $this->template->get('thumb_height', 'image');
				$thumb_file_details['file_out_extension'] = JFile::getExt($output_name);
				$thumb_file_details['mime_type'] = $this->findMimeType($original, $remote);

				// We need to resize the image and Save the new one only if it is in a different location
				$this->log->add('Create thumbnail from ' . $original . ' to ' . $thumb_file_details['file_out'], false);

				if ($original != $thumb_file_details['file_out'])
				{
					$new_img = $this->convertImage($thumb_file_details);

					// Check if an image was created
					if ($new_img)
					{
						// Get the details of the thumb image
						if (JFile::exists($new_img))
						{
							$this->log->add('Thumbnail created', false);

							return $output_path . $output_name;
						}
						else
						{
							$this->log->add('Thumbnail has not been created because the file ' . $new_img . ' does not exist', false);

							return false;
						}
					}
					else
					{
						$this->log->add('Thumnail has not been created because the image cannot be converted', false);

						return false;
					}
				}
				else
				{
					$this->log->add('Thumbnail is the same file and location as the original file', false);
					$this->log->AddStats('incorrect', 'COM_CSVI_THUMB_SAME_AS_FULL');

					return false;
				}
			}
			else
			{
				$this->log->add('File ' . $original . ' doest no exist, nothing to do', false);
				$this->log->AddStats('nofiles', JText::sprintf('COM_CSVI_FILE_DOES_NOT_EXIST_NOTHING_TO_DO', $original));

				return false;
			}
		}
		else
		{
			$this->log->add('Thumbnail size is too small', false);
			$this->log->AddStats('incorrect', 'COM_CSVI_THUMBNAIL_SIZE_TOO_SMALL');

			return false;
		}
	}

	/**
	 * Clean up the full image
	 *
	 * Clean up the image from any incorrect paths
	 *
	 * Minimum requirement is PHP 5.2.0
	 *
	 * [full_image] => Array
	 *  (
	 *      [isremote] => 1
	 *      [exists] => 1
	 *      [isimage] => 1
	 *      [name] => R05-01 -- R05-01 (700).jpg
	 *      [filename] => R05-01 -- R05-01 (700)
	 *      [extension] => jpg
	 *      [folder] => http://csvi3
	 *      [output_name] => R05-01 -- R05-01 (700).jpg
	 *      [output_filename] => R05-01 -- R05-01 (700)
	 *      [output_extension] => jpg
	 *      [output_folder] => http://csvi3
	 *      [mime_type] => image/jpeg
	 *  ).
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	private function cleanupImage()
	{
		$mime_type = '';

		if ($this->_imagedata['isremote'] && $this->template->get('save_images_on_server', 'image'))
		{
			// Check if we need to re-download the image
			$curfile = JPATH_SITE . '/' . $this->_imagedata['output_path'] . $this->_imagedata['output_name'];
			$this->log->add('Current file: ' . $curfile, false);

			// Check if the local file doet not exist or if it does but the user wants us to redownload the file
			if (!JFile::exists($curfile) || (JFile::exists($curfile) && $this->template->get('redownload_external_image', 'image')))
			{
				// Collect remote file information
				$local_image = CSVIPATH_TMP . '/' . $this->cleanFilename(basename($this->_imagedata['name']));

				// Store the remote image
				if ($this->storeRemoteImage($this->_imagedata['name'], $local_image))
				{
					$this->log->add('Remote file stored: ' . $this->_imagedata['name'] . ' --> ' . $local_image, false);

					// Update full image information
					$this->_imagedata['name'] = $local_image;

					// Get the mime type
					$mime_type = $this->findMimeType($local_image);
				}
				else
				{
					$this->log->AddStats('nofiles', JText::sprintf('COM_CSVI_REMOTE_FILE_NOT_FOUND', $this->_imagedata['name']));
					$this->log->add(JText::sprintf('COM_CSVI_REMOTE_FILE_NOT_FOUND', $this->_imagedata['name']), false);
				}
			}
			else
			{
				$mime_type = $this->findMimeType($curfile);
				$this->_imagedata['name'] = $this->_imagedata['output_path'] . $this->_imagedata['output_name'];
			}
		}
		elseif ($this->_imagedata['isremote'])
		{
			$mime_type = $this->findMimeType($this->_imagedata['name'], true);
			$this->_imagedata['output_path'] = dirname($this->_imagedata['name']) . '/';
		}
		elseif (!$this->_imagedata['isremote'])
		{
			$mime_type = $this->findMimeType($this->_imagedata['name']);
		}

		// Set the mime type
		$this->log->add('Mime type found: ' . $mime_type, false);
		$this->_imagedata['mime_type'] = $mime_type;

		// Validate extension against mime type
		$type = '';
		$ext = '';
		$mime_details = explode('/', $mime_type);

		if (isset($mime_details[0]))
		{
			$type = $mime_details[0];
		}

		if (isset($mime_details[1]))
		{
			$ext = $mime_details[1];
		}

		if ($ext == 'jpeg')
		{
			$ext = 'jpg';
		}

		// Get the extension of the target image name
		$output_ext = JFile::getExt($this->_imagedata['output_name']);

		if ($ext != strtolower($output_ext))
		{
			$this->log->add('Source extension is ' . $ext . ' and target extension is ' . $output_ext, false);

			// Fix up the new names
			$basename = basename($this->_imagedata['name'], '.' . $output_ext);
			$to = dirname($this->_imagedata['name']) . '/' . $basename . '.' . $ext;

			// Rename the file
			if (JFile::exists($this->_imagedata['name']))
			{
				$this->log->add('Renaming full image because bad extension: ' . $this->_imagedata['name'] . ' --> ' . $to, false);

				if (!JFile::move($this->_imagedata['name'], $to))
				{
					return false;
				}
				else
				{
					$this->_imagedata['name'] = $to;
				}
			}
		}

		// Check for a valid extenion
		if (empty($this->_imagedata['extension']) && $type === 'image')
		{
			$this->_imagedata['extension'] = $ext;
		}

		// Set a new extension if the image needs to be converted
		$convert_type = $this->template->get('convert_type', 'image');

		if ($convert_type !== 'none' && $convert_type !== $this->_imagedata['extension'])
		{
			// @todo Hier gaat het fout als de naam is gegeneerd op basis van SKU
			// Check if the name is generated
			if ($this->template->get('auto_generate_image_name', 'image', false))
			{
				$this->_imagedata['output_name'] = JFile::stripExt(basename($this->_imagedata['output_name'])) . '.' . $convert_type;
			}
			else
			{
				$this->_imagedata['output_name'] = JFile::stripExt(basename($this->_imagedata['name'])) . '.' . $convert_type;
			}

			$this->_imagedata['convert'] = true;
		}
		else
		{
			$this->_imagedata['convert'] = false;
		}

		// Set the file case
		$this->_imagedata['output_name'] = $this->setCase($this->_imagedata['output_name']);

		// Add some debug info
		$this->log->add('Full name original: ' . $this->_imagedata['name'], false);
		$this->log->add('Full name target: ' . $this->_imagedata['output_path'] . $this->_imagedata['output_name'], false);
	}

	/**
	 * Store a remote image on the local server.
	 *
	 * @param   string  $remote_image  The url of the remote image
	 * @param   string  $local_image   The full path and file name of the image to store
	 *
	 * @return  bool  True if remote file was locally written | False if remote file was not locally written.
	 *
	 * @since   3.0
	 */
	private function storeRemoteImage($remote_image, $local_image)
	{
		// Fix spaces in the remote image
		$dirPath      = dirname($remote_image);
		$imageName    = rawurlencode(basename($remote_image));
		$remote_image = $dirPath . '/' . $imageName;

		// Suppress any warnings as it breaks the import process
		$url_parts = @parse_url($remote_image);

		if (substr($url_parts['scheme'], 0, 4) == 'http')
		{
			$imageUsername  = $this->template->get('image_username', '');
			$imagePassword  = $this->template->get('image_password', '');
			$method         = $this->template->get('image_method', 'POST');
			$credentialType = $this->template->get('image_credential_type', 'htaccess');
			$http = JHttpFactory::getHttp(null, array('curl', 'stream'));

			if ($imageUsername && $imagePassword)
			{
				$data = array('user' => $imageUsername, 'password' => $imagePassword);

				if (strtoupper($method) === 'POST')
				{
					if ($credentialType === 'htaccess')
					{
						$http->setOption('userauth', $imageUsername);
						$http->setOption('passwordauth', $imagePassword);
						$data = array();
					}

					$answer = $http->post($remote_image, $data);
				}
				else
				{
					$answer = $http->get($remote_image, $data);
				}
			}
			else
			{
				$answer = $http->get($remote_image);
			}

			if ((int) $answer->code === 200)
			{
				return JFile::write($local_image, $answer->body);
			}

			return false;
		}
		elseif (substr($url_parts['scheme'], 0, 3) == 'ftp')
		{
			$host = $url_parts['host'];

			if ($host)
			{
				$port = (isset($url_parts['port'])) ? $url_parts['port'] : 21;
				$user = $url_parts['user'];
				$pass = $url_parts['pass'];

				$ftp = JClientFtp::getInstance($host, $port, array(), $user, $pass);
				$buffer = '';

				if ($ftp->read($url_parts['path'], $buffer))
				{
					return JFile::write($local_image, $buffer);
				}

				return false;
			}

			return false;
		}

		return false;
	}

	/**
	 * Convert the full image to another type.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   3.0
	 */
	private function convertFullImage()
	{
		// Let's see if the extensions are the same
		if ($this->_imagedata['convert'] && ($this->_imagedata['isremote'] && $this->template->get('save_images_on_server', 'image')))
		{
			// Collect the image details
			$file_details = array();
			$file_details['file'] = $this->_imagedata['name'];
			$file_details['file_extension'] = JFile::getExt($this->_imagedata['name']);
			$file_details['file_out'] = $this->_imagedata['base'] . '/' . $this->_imagedata['output_path'] . $this->_imagedata['output_name'];
			$file_details['maxsize'] = 0;
			$file_details['bgred'] = 255;
			$file_details['bggreen'] = 255;
			$file_details['bgblue'] = 255;
			$new_sizes = getimagesize($this->_imagedata['name']);
			$file_details['file_out_width'] = $new_sizes[0];
			$file_details['file_out_height'] = $new_sizes[1];
			$file_details['file_out_extension'] = JFile::getExt($this->_imagedata['output_name']);
			$file_details['mime_type'] = $this->_imagedata['mime_type'];

			// We need to resize the image and Save the new one (all done in the constructor)
			$this->log->add(JText::sprintf('COM_CSVI_DEBUG_CONVERT_IMAGE', $file_details['file'], $file_details['file_out']));
			$new_img = $this->convertImage($file_details);

			if ($new_img)
			{
				$this->log->add(JText::sprintf('COM_CSVI_IMAGE_CONVERTED', $file_details['file']));

				// See if we need to keep the old image
				if (!$this->template->get('keep_original', 'image') && JFile::exists($file_details['file']))
				{
					JFile::delete($file_details['file']);
				}

				// We have a new name, so refresh the info
				$this->_imagedata['name'] = dirname($this->_imagedata['name']) . '/' . $this->_imagedata['output_name'];
				$this->_imagedata['mime_type'] = $this->findMimeType(
					$this->_imagedata['base'] . '/' . $this->_imagedata['output_path'] . $this->_imagedata['output_name']
				);

				return true;
			}
			else
			{
				$this->log->add('Image not converted', false);

				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Rename image
	 *
	 * Rename an image, any existing file will be deleted.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	private function renameImage()
	{
		if (!$this->_imagedata['isremote']
			&& $this->template->get('auto_generate_image_name')
			&& $this->template->get('convert_type') === 'none'
			&& (basename($this->_imagedata['name']) !== $this->_imagedata['output_name']))
		{
			$from = $this->_imagedata['name'];

			if (JFile::exists($from))
			{
				$to = $this->_imagedata['base'] . '/' . $this->_imagedata['output_path'] . $this->_imagedata['output_name'];
				$this->log->add('Rename the full file from ' . $from . ' to ' . $to, false);

				// Delete existing target image
				if (JFile::exists($to))
				{
					JFile::delete($to);
				}

				// Check if the user wants to keep the original
				if ($this->template->get('keep_original'))
				{
					// Rename the image
					JFile::copy($from, $to);
				}
				else
				{
					// Rename the image
					JFile::move($from, $to);
				}
			}
			else
			{
				$this->log->add(JText::sprintf('COM_CSVI_RENAME_FULL_FILE_NOT_FOUND', $from), false);
			}
		}
	}

	/**
	 * Check if we need to convert the final image based on mime type.
	 *
	 * @return  void.
	 *
	 * @since   2.3.7
	 */
	private function imageTypeCheck()
	{
		// Get the output mime-type
		$output_ext = JFile::getExt($this->_imagedata['output_name']);

		if ($output_ext == 'jpg')
		{
			$mime_ext = 'jpeg';
		}
		else
		{
			$mime_ext = $output_ext;
		}

		// Check if the mime-type is different and if so, convert image
		if (!$this->_imagedata['isremote'] && JFile::exists($this->_imagedata['name']) && !stristr($this->_imagedata['mime_type'], $mime_ext))
		{
			$file_details = array();
			$file_details['file'] = $this->_imagedata['name'];
			$file_details['file_extension'] = JFile::getExt($this->_imagedata['name']);
			$file_details['maxsize'] = 0;
			$file_details['bgred'] = 255;
			$file_details['bggreen'] = 255;
			$file_details['bgblue'] = 255;
			$file_details['file_out'] = $this->_imagedata['base'] . '/' . $this->_imagedata['output_path'] . $this->_imagedata['output_name'];
			$new_sizes = getimagesize($this->_imagedata['name']);
			$file_details['file_out_width'] = $new_sizes[0];
			$file_details['file_out_height'] = $new_sizes[1];
			$file_details['file_out_extension'] = $output_ext;
			$file_details['mime_type'] = $this->_imagedata['mime_type'];

			// We need to resize the image and Save the new one (all done in the constructor)
			$this->log->add('Convert ' . $file_details['file'] . ' to ' . $file_details['file_out'], false);
			$new_img = $this->convertImage($file_details);

			if ($new_img)
			{
				$this->log->add('Converted the ' . $file_details['file'] . ' image', false);
			}
			else
			{
				$this->log->add('Could not converted the ' . $file_details['file'] . ' image', false);
			}
		}
		// We have a remote image, update the mime type since we can't convert images on remote servers
		elseif ($this->_imagedata['isremote'])
		{
			$mime_type = $this->findMimeType($this->_imagedata['output_path'] . $this->_imagedata['output_name'], true);

			if ($mime_type)
			{
				$this->_imagedata['mime_type'] = $mime_type;
			}
			else
			{
				$this->log->add('Unable to find the mime type on the remote file', false);
			}
		}
	}

	/**
	 * Clean filename
	 *
	 * Cleans up a filename and replaces non-supported characters with an underscore.
	 *
	 * @param   string  $value  The value to clean
	 *
	 * @return  string  The cleaned filename.
	 *
	 * @since   3.0
	 */
	private function cleanFilename($value)
	{
		$output = (string) preg_replace('/[^A-Z0-9_\.\s-]/i', '_', $value);

		return $output;
	}

	/**
	 * Change the case of any given string.
	 *
	 * @param   string  $name  The string to be case changed
	 *
	 * @return  string  The case changed string.
	 *
	 * @since   3.0
	 */
	private function setCase($name)
	{
		// Set the case if needed
		switch ($this->template->get('change_case', 'image'))
		{
			case 'lcase':
				return strtolower($name);
				break;
			case 'ucase':
				return strtoupper($name);
				break;
			case 'ucfirst':
				return ucfirst($name);
				break;
			case 'ucwords':
				return ucwords($name);
				break;
			default:
				return $name;
				break;
		}
	}

	/**
	 * Resize a large image.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	private function resizeFullImage()
	{
		// Check if we need to resize the full image
		if ($this->template->get('full_resize', 'image'))
		{
			// Get the current size
			$checkfile = $this->_imagedata['base'] . '/' . $this->_imagedata['output_path'] . $this->_imagedata['output_name'];

			if (JFile::exists($checkfile))
			{
				$cur_size = getimagesize($checkfile);

				if ($cur_size[0] > $this->template->get('full_width', 'image') || $cur_size[1] > $this->template->get('full_height', 'image'))
				{
					// Create a temporary file to work on
					JFile::copy($checkfile, CSVIPATH_TMP . '/' . $this->_imagedata['output_name']);

					// Resize the image
					$file_details = array();
					$file_details['file'] = CSVIPATH_TMP . '/' . $this->_imagedata['output_name'];
					$file_details['file_extension'] = JFile::getExt($checkfile);
					$file_details['rename'] = 0;
					$file_details['file_out'] = $checkfile;
					$file_details['maxsize'] = 0;
					$file_details['bgred'] = 255;
					$file_details['bggreen'] = 255;
					$file_details['bgblue'] = 255;
					$file_details['file_out_width'] = $this->template->get('full_width', 'image');
					$file_details['file_out_height'] = $this->template->get('full_height', 'image');
					$file_details['file_out_extension'] = JFile::getExt($checkfile);
					$file_details['mime_type'] = $this->_imagedata['mime_type'];

					// We need to resize the image and Save the new one (all done in the constructor)
					$this->log->add(
						sprintf(
							'Resizing large image %s from %s to %s',
							$file_details['file'], $cur_size[1] . 'x' . $cur_size[0], $this->template->get('full_height') . 'x' . $this->template->get('full_width')
						)
					);

					JFile::delete($checkfile);
					$new_img = $this->convertImage($file_details);

					// Delete the temporary file
					JFile::delete(CSVIPATH_TMP . '/' . $this->_imagedata['output_name']);

					if ($new_img)
					{
						$this->log->add('Full image has been resized');
					}
				}
			}
		}
	}

	/**
	 * Add a watermark to an image.
	 *
	 * @param   string  $imagename  The full path of the image to watermark
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   4.2
	 */
	public function addWatermark($imagename)
	{
		$watermark = JPATH_SITE . '/' . $this->template->get('full_watermark_image');
		$result = false;

		// Clear the cache
		clearstatcache();

		// Check if the files exist
		if (file_exists($imagename) && file_exists($watermark))
		{
			$mime_image = $this->findMimeType($imagename);
			$this->log->add('Mime image:' . $mime_image, false);
			$image = $this->createImage($mime_image, $imagename);
			$ext = JFile::getExt($imagename);

			if ($image)
			{
				$mime_stamp = $this->findMimeType($watermark);
				$this->log->add('Mime stamp:' . $mime_stamp, false);
				$stamp = $this->createImage($mime_stamp, $watermark);

				// Set the margins for the stamp and get the height/width of the stamp image
				$marge_right = $this->template->get('full_watermark_right', 0);
				$marge_bottom = $this->template->get('full_watermark_bottom', 0);
				$sx = imagesx($stamp);
				$sy = imagesy($stamp);

				// Copy the stamp image onto our photo using the margin offsets and the photo
				// width to calculate positioning of the stamp.
				imagecopy($image, $stamp, imagesx($image) - $sx - $marge_right, imagesy($image) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));

				// Save the new image
				switch ($ext)
				{
					case "gif":
						$result = imagegif($image, $imagename);
						break;
					case "jpg":
					case "jpeg":
						$result = imagejpeg($image, $imagename, 100);
						break;
					case "png":
						$result = imagepng($image, $imagename);
						break;
					default:
						$this->log->add('No file extension found', false);
						break;
				}

				imagedestroy($image);
			}
			else
			{
				$this->log->add('Cannot create watermark file', false);
			}
		}
		else
		{
			$this->log->add('Files ' . $imagename . ' and ' . $watermark . ' do not exist', false);

			return false;
		}

		return $result;
	}

	/**
	 * Create an image object.
	 *
	 * @param   string  $mime_type  The mime type of the image
	 * @param   string  $imagename  The full path image to create
	 *
	 * @return  mixed  Image resource on success | False on failure.
	 *
	 * @since   4.2
	 */
	private function createImage($mime_type, $imagename)
	{
		$image = null;

		switch ($mime_type)
		{
			case 'image/gif':
				if (function_exists('imagecreatefromgif'))
				{
					$image = @imagecreatefromgif($imagename);
				}
				else
				{
					return false;
				}
				break;
			case 'image/jpg':
			case 'image/jpeg':
				if (function_exists('imagecreatefromjpeg'))
				{
					$image = @imagecreatefromjpeg($imagename);
				}
				else
				{
					return false;
				}
				break;
			case 'image/png':
				if (function_exists('imagecreatefrompng'))
				{
					$image = @imagecreatefrompng($imagename);
				}
				else
				{
					return false;
				}
				break;
			default:
				return false;
				break;
		}

		return $image;
	}
}
