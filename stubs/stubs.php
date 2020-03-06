<?php
class JRegistry extends \Joomla\Registry\Registry {}
abstract class JRegistryFormat extends \Joomla\Registry\AbstractRegistryFormat {}
class JRegistryFormatIni extends \Joomla\Registry\Format\Ini {}
class JRegistryFormatJson extends \Joomla\Registry\Format\Json {}
class JRegistryFormatPhp extends \Joomla\Registry\Format\Php {}
class JRegistryFormatXml extends \Joomla\Registry\Format\Xml {}
class JStringInflector extends \Joomla\String\Inflector {}
abstract class JStringNormalise extends \Joomla\String\Normalise {}
class JData extends \Joomla\Data\DataObject {}
class JDataSet extends \Joomla\Data\DataSet {}
interface JDataDumpable extends \Joomla\Data\DumpableInterface {}
class JApplicationAdministrator extends \Joomla\CMS\Application\AdministratorApplication {}
class JApplicationHelper extends \Joomla\CMS\Application\ApplicationHelper {}
abstract class JApplicationBase extends \Joomla\CMS\Application\BaseApplication {}
class JApplicationCli extends \Joomla\CMS\Application\CliApplication {}
class JApplicationCms extends \Joomla\CMS\Application\CMSApplication {}
class JApplicationDaemon extends \Joomla\CMS\Application\DaemonApplication {}
class JApplicationSite extends \Joomla\CMS\Application\SiteApplication {}
class JApplicationWeb extends \Joomla\CMS\Application\WebApplication {}
class JApplicationWebClient extends \Joomla\Application\Web\WebClient {}
class JDaemon extends \Joomla\CMS\Application\DaemonApplication {}
class JCli extends \Joomla\CMS\Application\CliApplication {}
class JWeb extends \Joomla\CMS\Application\WebApplication {}
class JWebClient extends \Joomla\Application\Web\WebClient {}
abstract class JModelAdmin extends \Joomla\CMS\MVC\Model\AdminModel {}
abstract class JModelForm extends \Joomla\CMS\MVC\Model\FormModel {}
abstract class JModelItem extends \Joomla\CMS\MVC\Model\ItemModel {}
class JModelList extends \Joomla\CMS\MVC\Model\ListModel {}
abstract class JModelLegacy extends \Joomla\CMS\MVC\Model\BaseDatabaseModel {}
class JViewCategories extends \Joomla\CMS\MVC\View\CategoriesView {}
class JViewCategory extends \Joomla\CMS\MVC\View\CategoryView {}
class JViewCategoryfeed extends \Joomla\CMS\MVC\View\CategoryFeedView {}
class JViewLegacy extends \Joomla\CMS\MVC\View\HtmlView {}
class JControllerAdmin extends \Joomla\CMS\MVC\Controller\AdminController {}
class JControllerLegacy extends \Joomla\CMS\MVC\Controller\BaseController {}
class JControllerForm extends \Joomla\CMS\MVC\Controller\FormController {}
interface JTableInterface extends \Joomla\CMS\Table\TableInterface {}
abstract class JTable extends \Joomla\CMS\Table\Table {}
class JTableNested extends \Joomla\CMS\Table\Nested {}
class JTableAsset extends \Joomla\CMS\Table\Asset {}
class JTableExtension extends \Joomla\CMS\Table\Extension {}
class JTableLanguage extends \Joomla\CMS\Table\Language {}
class JTableUpdate extends \Joomla\CMS\Table\Update {}
class JTableUpdatesite extends \Joomla\CMS\Table\UpdateSite {}
class JTableUser extends \Joomla\CMS\Table\User {}
class JTableUsergroup extends \Joomla\CMS\Table\Usergroup {}
class JTableViewlevel extends \Joomla\CMS\Table\ViewLevel {}
class JTableContenthistory extends \Joomla\CMS\Table\ContentHistory {}
class JTableContenttype extends \Joomla\CMS\Table\ContentType {}
class JTableCorecontent extends \Joomla\CMS\Table\CoreContent {}
class JTableUcm extends \Joomla\CMS\Table\Ucm {}
class JTableCategory extends \Joomla\CMS\Table\Category {}
class JTableContent extends \Joomla\CMS\Table\Content {}
class JTableMenu extends \Joomla\CMS\Table\Menu {}
class JTableMenuType extends \Joomla\CMS\Table\MenuType {}
class JTableModule extends \Joomla\CMS\Table\Module {}
abstract class JTableObserver extends \Joomla\CMS\Table\Observer\AbstractObserver {}
class JTableObserverContenthistory extends \Joomla\CMS\Table\Observer\ContentHistory {}
class JTableObserverTags extends \Joomla\CMS\Table\Observer\Tags {}
class JAccess extends \Joomla\CMS\Access\Access {}
class JAccessRule extends \Joomla\CMS\Access\Rule {}
class JAccessRules extends \Joomla\CMS\Access\Rules {}
class JAccessWrapperAccess extends \Joomla\CMS\Access\Wrapper\Access {}
class JAccessExceptionNotallowed extends \Joomla\CMS\Access\Exception\NotAllowed {}
class JRule extends \Joomla\CMS\Access\Rule {}
class JRules extends \Joomla\CMS\Access\Rules {}
class JHelp extends \Joomla\CMS\Help\Help {}
class JCaptcha extends \Joomla\CMS\Captcha\Captcha {}
class JLanguageAssociations extends \Joomla\CMS\Language\Associations {}
class JLanguage extends \Joomla\CMS\Language\Language {}
class JLanguageHelper extends \Joomla\CMS\Language\LanguageHelper {}
abstract class JLanguageStemmer extends \Joomla\CMS\Language\LanguageStemmer {}
class JLanguageMultilang extends \Joomla\CMS\Language\Multilanguage {}
class JText extends \Joomla\CMS\Language\Text {}
class JLanguageTransliterate extends \Joomla\CMS\Language\Transliterate {}
class JLanguageStemmerPorteren extends \Joomla\CMS\Language\Stemmer\Porteren {}
class JLanguageWrapperText extends \Joomla\CMS\Language\Wrapper\JTextWrapper {}
class JLanguageWrapperHelper extends \Joomla\CMS\Language\Wrapper\LanguageHelperWrapper {}
class JLanguageWrapperTransliterate extends \Joomla\CMS\Language\Wrapper\TransliterateWrapper {}
class JComponentHelper extends \Joomla\CMS\Component\ComponentHelper {}
class JComponentRecord extends \Joomla\CMS\Component\ComponentRecord {}
class JComponentExceptionMissing extends \Joomla\CMS\Component\Exception\MissingComponentException {}
abstract class JComponentRouterBase extends \Joomla\CMS\Component\Router\RouterBase {}
interface JComponentRouterInterface extends \Joomla\CMS\Component\Router\RouterInterface {}
class JComponentRouterLegacy extends \Joomla\CMS\Component\Router\RouterLegacy {}
abstract class JComponentRouterView extends \Joomla\CMS\Component\Router\RouterView {}
class JComponentRouterViewconfiguration extends \Joomla\CMS\Component\Router\RouterViewConfiguration {}
class JComponentRouterRulesMenu extends \Joomla\CMS\Component\Router\Rules\MenuRules {}
class JComponentRouterRulesNomenu extends \Joomla\CMS\Component\Router\Rules\NomenuRules {}
interface JComponentRouterRulesInterface extends \Joomla\CMS\Component\Router\Rules\RulesInterface {}
class JComponentRouterRulesStandard extends \Joomla\CMS\Component\Router\Rules\StandardRules {}
class JEditor extends \Joomla\CMS\Editor\Editor {}
class JErrorPage extends \Joomla\CMS\Exception\ExceptionHandler {}
abstract class JAuthenticationHelper extends \Joomla\CMS\Helper\AuthenticationHelper {}
class JHelper extends \Joomla\CMS\Helper\CMSHelper {}
class JHelperContent extends \Joomla\CMS\Helper\ContentHelper {}
class JHelperContenthistory extends \Joomla\CMS\Helper\ContentHistoryHelper {}
class JLibraryHelper extends \Joomla\CMS\Helper\LibraryHelper {}
class JHelperMedia extends \Joomla\CMS\Helper\MediaHelper {}
abstract class JModuleHelper extends \Joomla\CMS\Helper\ModuleHelper {}
class JHelperRoute extends \Joomla\CMS\Helper\RouteHelper {}
class JSearchHelper extends \Joomla\CMS\Helper\SearchHelper {}
class JHelperTags extends \Joomla\CMS\Helper\TagsHelper {}
class JHelperUsergroups extends \Joomla\CMS\Helper\UserGroupsHelper {}
class JLayoutBase extends \Joomla\CMS\Layout\BaseLayout {}
class JLayoutFile extends \Joomla\CMS\Layout\FileLayout {}
class JLayoutHelper extends \Joomla\CMS\Layout\LayoutHelper {}
interface JLayout extends \Joomla\CMS\Layout\LayoutInterface {}
class JResponseJson extends \Joomla\CMS\Response\JsonResponse {}
abstract class JPlugin extends \Joomla\CMS\Plugin\CMSPlugin {}
abstract class JPluginHelper extends \Joomla\CMS\Plugin\PluginHelper {}
class JMenu extends \Joomla\CMS\Menu\AbstractMenu {}
class JMenuAdministrator extends \Joomla\CMS\Menu\AdministratorMenu {}
class JMenuItem extends \Joomla\CMS\Menu\MenuItem {}
class JMenuSite extends \Joomla\CMS\Menu\SiteMenu {}
class JPagination extends \Joomla\CMS\Pagination\Pagination {}
class JPaginationObject extends \Joomla\CMS\Pagination\PaginationObject {}
class JPathway extends \Joomla\CMS\Pathway\Pathway {}
class JPathwaySite extends \Joomla\CMS\Pathway\SitePathway {}
abstract class JSchemaChangeitem extends \Joomla\CMS\Schema\ChangeItem {}
class JSchemaChangeset extends \Joomla\CMS\Schema\ChangeSet {}
class JSchemaChangeitemMysql extends \Joomla\CMS\Schema\ChangeItem\MysqlChangeItem {}
class JSchemaChangeitemPostgresql extends \Joomla\CMS\Schema\ChangeItem\PostgresqlChangeItem {}
class JSchemaChangeitemSqlsrv extends \Joomla\CMS\Schema\ChangeItem\SqlsrvChangeItem {}
interface JUcm extends \Joomla\CMS\UCM\UCM {}
class JUcmBase extends \Joomla\CMS\UCM\UCMBase {}
class JUcmContent extends \Joomla\CMS\UCM\UCMContent {}
class JUcmType extends \Joomla\CMS\UCM\UCMType {}
class JToolbar extends \Joomla\CMS\Toolbar\Toolbar {}
abstract class JToolbarButton extends \Joomla\CMS\Toolbar\ToolbarButton {}
class JToolbarButtonConfirm extends \Joomla\CMS\Toolbar\Button\ConfirmButton {}
class JToolbarButtonCustom extends \Joomla\CMS\Toolbar\Button\CustomButton {}
class JToolbarButtonHelp extends \Joomla\CMS\Toolbar\Button\HelpButton {}
class JToolbarButtonLink extends \Joomla\CMS\Toolbar\Button\LinkButton {}
class JToolbarButtonPopup extends \Joomla\CMS\Toolbar\Button\PopupButton {}
class JToolbarButtonSeparator extends \Joomla\CMS\Toolbar\Button\SeparatorButton {}
class JToolbarButtonSlider extends \Joomla\CMS\Toolbar\Button\SliderButton {}
class JToolbarButtonStandard extends \Joomla\CMS\Toolbar\Button\StandardButton {}
abstract class JButton extends \Joomla\CMS\Toolbar\ToolbarButton {}
class JVersion extends \Joomla\CMS\Version {}
class JAuthentication extends \Joomla\CMS\Authentication\Authentication {}
class JAuthenticationResponse extends \Joomla\CMS\Authentication\AuthenticationResponse {}
class JBrowser extends \Joomla\CMS\Environment\Browser {}
interface JAssociationExtensionInterface extends \Joomla\CMS\Association\AssociationExtensionInterface {}
abstract class JAssociationExtensionHelper extends \Joomla\CMS\Association\AssociationExtensionHelper {}
class JDocument extends \Joomla\CMS\Document\Document {}
class JDocumentError extends \Joomla\CMS\Document\ErrorDocument {}
class JDocumentFeed extends \Joomla\CMS\Document\FeedDocument {}
class JDocumentHtml extends \Joomla\CMS\Document\HtmlDocument {}
class JDocumentImage extends \Joomla\CMS\Document\ImageDocument {}
class JDocumentJson extends \Joomla\CMS\Document\JsonDocument {}
class JDocumentOpensearch extends \Joomla\CMS\Document\OpensearchDocument {}
class JDocumentRaw extends \Joomla\CMS\Document\RawDocument {}
class JDocumentRenderer extends \Joomla\CMS\Document\DocumentRenderer {}
class JDocumentXml extends \Joomla\CMS\Document\XmlDocument {}
class JDocumentRendererFeedAtom extends \Joomla\CMS\Document\Renderer\Feed\AtomRenderer {}
class JDocumentRendererFeedRss extends \Joomla\CMS\Document\Renderer\Feed\RssRenderer {}
class JDocumentRendererHtmlComponent extends \Joomla\CMS\Document\Renderer\Html\ComponentRenderer {}
class JDocumentRendererHtmlHead extends \Joomla\CMS\Document\Renderer\Html\HeadRenderer {}
class JDocumentRendererHtmlMessage extends \Joomla\CMS\Document\Renderer\Html\MessageRenderer {}
class JDocumentRendererHtmlModule extends \Joomla\CMS\Document\Renderer\Html\ModuleRenderer {}
class JDocumentRendererHtmlModules extends \Joomla\CMS\Document\Renderer\Html\ModulesRenderer {}
class JDocumentRendererAtom extends \Joomla\CMS\Document\Renderer\Feed\AtomRenderer {}
class JDocumentRendererRSS extends \Joomla\CMS\Document\Renderer\Feed\RssRenderer {}
class JDocumentRendererComponent extends \Joomla\CMS\Document\Renderer\Html\ComponentRenderer {}
class JDocumentRendererHead extends \Joomla\CMS\Document\Renderer\Html\HeadRenderer {}
class JDocumentRendererMessage extends \Joomla\CMS\Document\Renderer\Html\MessageRenderer {}
class JDocumentRendererModule extends \Joomla\CMS\Document\Renderer\Html\ModuleRenderer {}
class JDocumentRendererModules extends \Joomla\CMS\Document\Renderer\Html\ModulesRenderer {}
class JFeedEnclosure extends \Joomla\CMS\Document\Feed\FeedEnclosure {}
class JFeedImage extends \Joomla\CMS\Document\Feed\FeedImage {}
class JFeedItem extends \Joomla\CMS\Document\Feed\FeedItem {}
class JOpenSearchImage extends \Joomla\CMS\Document\Opensearch\OpensearchImage {}
class JOpenSearchUrl extends \Joomla\CMS\Document\Opensearch\OpensearchUrl {}
class JFilterInput extends \Joomla\CMS\Filter\InputFilter {}
class JFilterOutput extends \Joomla\CMS\Filter\OutputFilter {}
class JFilterWrapperOutput extends \Joomla\CMS\Filter\Wrapper\OutputFilterWrapper {}
class JHttp extends \Joomla\CMS\Http\Http {}
class JHttpFactory extends \Joomla\CMS\Http\HttpFactory {}
class JHttpResponse extends \Joomla\CMS\Http\Response {}
interface JHttpTransport extends \Joomla\CMS\Http\TransportInterface {}
class JHttpTransportCurl extends \Joomla\CMS\Http\Transport\CurlTransport {}
class JHttpTransportSocket extends \Joomla\CMS\Http\Transport\SocketTransport {}
class JHttpTransportStream extends \Joomla\CMS\Http\Transport\StreamTransport {}
class JHttpWrapperFactory extends \Joomla\CMS\Http\Wrapper\FactoryWrapper {}
class JInstaller extends \Joomla\CMS\Installer\Installer {}
abstract class JInstallerAdapter extends \Joomla\CMS\Installer\InstallerAdapter {}
class JInstallerExtension extends \Joomla\CMS\Installer\InstallerExtension {}
class JExtension extends \Joomla\CMS\Installer\InstallerExtension {}
abstract class JInstallerHelper extends \Joomla\CMS\Installer\InstallerHelper {}
class JInstallerScript extends \Joomla\CMS\Installer\InstallerScript {}
abstract class JInstallerManifest extends \Joomla\CMS\Installer\Manifest {}
class JInstallerAdapterComponent extends \Joomla\CMS\Installer\Adapter\ComponentAdapter {}
class JInstallerComponent extends \Joomla\CMS\Installer\Adapter\ComponentAdapter {}
class JInstallerAdapterFile extends \Joomla\CMS\Installer\Adapter\FileAdapter {}
class JInstallerFile extends \Joomla\CMS\Installer\Adapter\FileAdapter {}
class JInstallerAdapterLanguage extends \Joomla\CMS\Installer\Adapter\LanguageAdapter {}
class JInstallerLanguage extends \Joomla\CMS\Installer\Adapter\LanguageAdapter {}
class JInstallerAdapterLibrary extends \Joomla\CMS\Installer\Adapter\LibraryAdapter {}
class JInstallerLibrary extends \Joomla\CMS\Installer\Adapter\LibraryAdapter {}
class JInstallerAdapterModule extends \Joomla\CMS\Installer\Adapter\ModuleAdapter {}
class JInstallerModule extends \Joomla\CMS\Installer\Adapter\ModuleAdapter {}
class JInstallerAdapterPackage extends \Joomla\CMS\Installer\Adapter\PackageAdapter {}
class JInstallerPackage extends \Joomla\CMS\Installer\Adapter\PackageAdapter {}
class JInstallerAdapterPlugin extends \Joomla\CMS\Installer\Adapter\PluginAdapter {}
class JInstallerPlugin extends \Joomla\CMS\Installer\Adapter\PluginAdapter {}
class JInstallerAdapterTemplate extends \Joomla\CMS\Installer\Adapter\TemplateAdapter {}
class JInstallerTemplate extends \Joomla\CMS\Installer\Adapter\TemplateAdapter {}
class JInstallerManifestLibrary extends \Joomla\CMS\Installer\Manifest\LibraryManifest {}
class JInstallerManifestPackage extends \Joomla\CMS\Installer\Manifest\PackageManifest {}
class JRouterAdministrator extends \Joomla\CMS\Router\AdministratorRouter {}
class JRoute extends \Joomla\CMS\Router\Route {}
class JRouter extends \Joomla\CMS\Router\Router {}
class JRouterSite extends \Joomla\CMS\Router\SiteRouter {}
class JCategories extends \Joomla\CMS\Categories\Categories {}
class JCategoryNode extends \Joomla\CMS\Categories\CategoryNode {}
class JDate extends \Joomla\CMS\Date\Date {}
class JLog extends \Joomla\CMS\Log\Log {}
class JLogEntry extends \Joomla\CMS\Log\LogEntry {}
abstract class JLogLogger extends \Joomla\CMS\Log\Logger {}
abstract class JLogger extends \Joomla\CMS\Log\Logger {}
class JLogLoggerCallback extends \Joomla\CMS\Log\Logger\CallbackLogger {}
class JLogLoggerDatabase extends \Joomla\CMS\Log\Logger\DatabaseLogger {}
class JLogLoggerEcho extends \Joomla\CMS\Log\Logger\EchoLogger {}
class JLogLoggerFormattedtext extends \Joomla\CMS\Log\Logger\FormattedtextLogger {}
class JLogLoggerMessagequeue extends \Joomla\CMS\Log\Logger\MessagequeueLogger {}
class JLogLoggerSyslog extends \Joomla\CMS\Log\Logger\SyslogLogger {}
class JLogLoggerW3c extends \Joomla\CMS\Log\Logger\W3cLogger {}
class JProfiler extends \Joomla\CMS\Profiler\Profiler {}
class JUri extends \Joomla\CMS\Uri\Uri {}
class JCache extends \Joomla\CMS\Cache\Cache {}
class JCacheController extends \Joomla\CMS\Cache\CacheController {}
class JCacheStorage extends \Joomla\CMS\Cache\CacheStorage {}
class JCacheControllerCallback extends \Joomla\CMS\Cache\Controller\CallbackController {}
class JCacheControllerOutput extends \Joomla\CMS\Cache\Controller\OutputController {}
class JCacheControllerPage extends \Joomla\CMS\Cache\Controller\PageController {}
class JCacheControllerView extends \Joomla\CMS\Cache\Controller\ViewController {}
class JCacheStorageApc extends \Joomla\CMS\Cache\Storage\ApcStorage {}
class JCacheStorageApcu extends \Joomla\CMS\Cache\Storage\ApcuStorage {}
class JCacheStorageHelper extends \Joomla\CMS\Cache\Storage\CacheStorageHelper {}
class JCacheStorageCachelite extends \Joomla\CMS\Cache\Storage\CacheliteStorage {}
class JCacheStorageFile extends \Joomla\CMS\Cache\Storage\FileStorage {}
class JCacheStorageMemcached extends \Joomla\CMS\Cache\Storage\MemcachedStorage {}
class JCacheStorageMemcache extends \Joomla\CMS\Cache\Storage\MemcacheStorage {}
class JCacheStorageRedis extends \Joomla\CMS\Cache\Storage\RedisStorage {}
class JCacheStorageWincache extends \Joomla\CMS\Cache\Storage\WincacheStorage {}
class JCacheStorageXcache extends \Joomla\CMS\Cache\Storage\XcacheStorage {}
interface JCacheException extends \Joomla\CMS\Cache\Exception\CacheExceptionInterface {}
class JCacheExceptionConnecting extends \Joomla\CMS\Cache\Exception\CacheConnectingException {}
class JCacheExceptionUnsupported extends \Joomla\CMS\Cache\Exception\UnsupportedCacheException {}
class JSession extends \Joomla\CMS\Session\Session {}
class JSessionExceptionUnsupported extends \Joomla\CMS\Session\Exception\UnsupportedStorageException {}
class JUser extends \Joomla\CMS\User\User {}
abstract class JUserHelper extends \Joomla\CMS\User\UserHelper {}
class JUserWrapperHelper extends \Joomla\CMS\User\UserWrapper {}
class JForm extends \Joomla\CMS\Form\Form {}
abstract class JFormField extends \Joomla\CMS\Form\FormField {}
class JFormHelper extends \Joomla\CMS\Form\FormHelper {}
class JFormRule extends \Joomla\CMS\Form\FormRule {}
class JFormWrapper extends \Joomla\CMS\Form\FormWrapper {}
class JFormFieldAuthor extends \Joomla\CMS\Form\Field\AuthorField {}
class JFormFieldCaptcha extends \Joomla\CMS\Form\Field\CaptchaField {}
class JFormFieldChromeStyle extends \Joomla\CMS\Form\Field\ChromestyleField {}
class JFormFieldContenthistory extends \Joomla\CMS\Form\Field\ContenthistoryField {}
class JFormFieldContentlanguage extends \Joomla\CMS\Form\Field\ContentlanguageField {}
class JFormFieldContenttype extends \Joomla\CMS\Form\Field\ContenttypeField {}
class JFormFieldEditor extends \Joomla\CMS\Form\Field\EditorField {}
class JFormFieldFrontend_Language extends \Joomla\CMS\Form\Field\FrontendlanguageField {}
class JFormFieldHeadertag extends \Joomla\CMS\Form\Field\HeadertagField {}
class JFormFieldHelpsite extends \Joomla\CMS\Form\Field\HelpsiteField {}
class JFormFieldLastvisitDateRange extends \Joomla\CMS\Form\Field\LastvisitdaterangeField {}
class JFormFieldLimitbox extends \Joomla\CMS\Form\Field\LimitboxField {}
class JFormFieldMedia extends \Joomla\CMS\Form\Field\MediaField {}
class JFormFieldMenu extends \Joomla\CMS\Form\Field\MenuField {}
class JFormFieldMenuitem extends \Joomla\CMS\Form\Field\MenuitemField {}
class JFormFieldModuleOrder extends \Joomla\CMS\Form\Field\ModuleorderField {}
class JFormFieldModulePosition extends \Joomla\CMS\Form\Field\ModulepositionField {}
class JFormFieldModuletag extends \Joomla\CMS\Form\Field\ModuletagField {}
class JFormFieldOrdering extends \Joomla\CMS\Form\Field\OrderingField {}
class JFormFieldPlugin_Status extends \Joomla\CMS\Form\Field\PluginstatusField {}
class JFormFieldRedirect_Status extends \Joomla\CMS\Form\Field\RedirectStatusField {}
class JFormFieldRegistrationDateRange extends \Joomla\CMS\Form\Field\RegistrationdaterangeField {}
class JFormFieldStatus extends \Joomla\CMS\Form\Field\StatusField {}
class JFormFieldTag extends \Joomla\CMS\Form\Field\TagField {}
class JFormFieldTemplatestyle extends \Joomla\CMS\Form\Field\TemplatestyleField {}
class JFormFieldUserActive extends \Joomla\CMS\Form\Field\UseractiveField {}
class JFormFieldUserGroupList extends \Joomla\CMS\Form\Field\UsergrouplistField {}
class JFormFieldUserState extends \Joomla\CMS\Form\Field\UserstateField {}
class JFormFieldUser extends \Joomla\CMS\Form\Field\UserField {}
class JFormRuleBoolean extends \Joomla\CMS\Form\Rule\BooleanRule {}
class JFormRuleCalendar extends \Joomla\CMS\Form\Rule\CalendarRule {}
class JFormRuleCaptcha extends \Joomla\CMS\Form\Rule\CaptchaRule {}
class JFormRuleColor extends \Joomla\CMS\Form\Rule\ColorRule {}
class JFormRuleEmail extends \Joomla\CMS\Form\Rule\EmailRule {}
class JFormRuleEquals extends \Joomla\CMS\Form\Rule\EqualsRule {}
class JFormRuleNotequals extends \Joomla\CMS\Form\Rule\NotequalsRule {}
class JFormRuleNumber extends \Joomla\CMS\Form\Rule\NumberRule {}
class JFormRuleOptions extends \Joomla\CMS\Form\Rule\OptionsRule {}
class JFormRulePassword extends \Joomla\CMS\Form\Rule\PasswordRule {}
class JFormRuleRules extends \Joomla\CMS\Form\Rule\RulesRule {}
class JFormRuleTel extends \Joomla\CMS\Form\Rule\TelRule {}
class JFormRuleUrl extends \Joomla\CMS\Form\Rule\UrlRule {}
class JFormRuleUsername extends \Joomla\CMS\Form\Rule\UsernameRule {}
class JMicrodata extends \Joomla\CMS\Microdata\Microdata {}
abstract class JFactory extends \Joomla\CMS\Factory {}
class JMail extends \Joomla\CMS\Mail\Mail {}
abstract class JMailHelper extends \Joomla\CMS\Mail\MailHelper {}
class JMailWrapperHelper extends \Joomla\CMS\Mail\MailWrapper {}
class JClientHelper extends \Joomla\CMS\Client\ClientHelper {}
class JClientWrapperHelper extends \Joomla\CMS\Client\ClientWrapper {}
class JClientFtp extends \Joomla\CMS\Client\FtpClient {}
class JFTP extends \Joomla\CMS\Client\FtpClient {}
class JClientLdap extends \Joomla\Ldap\LdapClient {}
class JLDAP extends \Joomla\Ldap\LdapClient {}
class JUpdate extends \Joomla\CMS\Updater\Update {}
abstract class JUpdateAdapter extends \Joomla\CMS\Updater\UpdateAdapter {}
class JUpdater extends \Joomla\CMS\Updater\Updater {}
class JUpdaterCollection extends \Joomla\CMS\Updater\Adapter\CollectionAdapter {}
class JUpdaterExtension extends \Joomla\CMS\Updater\Adapter\ExtensionAdapter {}
class JCrypt extends \Joomla\CMS\Crypt\Crypt {}
interface JCryptCipher extends \Joomla\CMS\Crypt\CipherInterface {}
class JCryptKey extends \Joomla\CMS\Crypt\Key {}
interface JCryptPassword extends \Joomla\CMS\Crypt\CryptPassword {}
class JCryptCipherBlowfish extends \Joomla\CMS\Crypt\Cipher\BlowfishCipher {}
class JCryptCipherCrypto extends \Joomla\CMS\Crypt\Cipher\CryptoCipher {}
abstract class JCryptCipherMcrypt extends \Joomla\CMS\Crypt\Cipher\McryptCipher {}
class JCryptCipherRijndael256 extends \Joomla\CMS\Crypt\Cipher\Rijndael256Cipher {}
class JCryptCipherSimple extends \Joomla\CMS\Crypt\Cipher\SimpleCipher {}
class JCryptCipherSodium extends \Joomla\CMS\Crypt\Cipher\SodiumCipher {}
class JCryptCipher3Des extends \Joomla\CMS\Crypt\Cipher\TripleDesCipher {}
class JCryptPasswordSimple extends \Joomla\CMS\Crypt\Password\SimpleCryptPassword {}
abstract class JStringPunycode extends \Joomla\CMS\String\PunycodeHelper {}
class JBuffer extends \Joomla\CMS\Utility\BufferStreamHandler {}
class JUtility extends \Joomla\CMS\Utility\Utility {}
class JInputCli extends \Joomla\CMS\Input\Cli {}
class JInputCookie extends \Joomla\CMS\Input\Cookie {}
class JInputFiles extends \Joomla\CMS\Input\Files {}
class JInput extends \Joomla\CMS\Input\Input {}
class JInputJSON extends \Joomla\CMS\Input\Json {}
class JFeed extends \Joomla\CMS\Feed\Feed {}
class JFeedEntry extends \Joomla\CMS\Feed\FeedEntry {}
class JFeedFactory extends \Joomla\CMS\Feed\FeedFactory {}
class JFeedLink extends \Joomla\CMS\Feed\FeedLink {}
abstract class JFeedParser extends \Joomla\CMS\Feed\FeedParser {}
class JFeedPerson extends \Joomla\CMS\Feed\FeedPerson {}
class JFeedParserAtom extends \Joomla\CMS\Feed\Parser\AtomParser {}
interface JFeedParserNamespace extends \Joomla\CMS\Feed\Parser\NamespaceParserInterface {}
class JFeedParserRss extends \Joomla\CMS\Feed\Parser\RssParser {}
class JFeedParserRssItunes extends \Joomla\CMS\Feed\Parser\Rss\ItunesRssParser {}
class JFeedParserRssMedia extends \Joomla\CMS\Feed\Parser\Rss\MediaRssParser {}
class JImage extends \Joomla\CMS\Image\Image {}
abstract class JImageFilter extends \Joomla\CMS\Image\ImageFilter {}
class JImageFilterBackgroundfill extends \Joomla\Image\Filter\Backgroundfill {}
class JImageFilterBrightness extends \Joomla\Image\Filter\Brightness {}
class JImageFilterContrast extends \Joomla\Image\Filter\Contrast {}
class JImageFilterEdgedetect extends \Joomla\Image\Filter\Edgedetect {}
class JImageFilterEmboss extends \Joomla\Image\Filter\Emboss {}
class JImageFilterNegate extends \Joomla\Image\Filter\Negate {}
class JImageFilterSketchy extends \Joomla\Image\Filter\Sketchy {}
class JImageFilterSmooth extends \Joomla\Image\Filter\Smooth {}
class JObject extends \Joomla\CMS\Object\CMSObject {}
class JExtensionHelper extends \Joomla\CMS\Extension\ExtensionHelper {}
abstract class JHtml extends \Joomla\CMS\HTML\HTMLHelper {}
class JAdministrator extends JApplicationAdministrator {}
class JSite extends JApplicationSite {}
