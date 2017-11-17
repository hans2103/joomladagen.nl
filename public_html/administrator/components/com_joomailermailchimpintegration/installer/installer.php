<?php
/**
 * Copyright (C) 2015  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

// no direct access
defined('_JEXEC') or die('Restricted Access'); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta charset="utf-8"/>
    <title>joomlamailer Installer</title>
    <link href="<?php echo JURI::root(); ?>administrator/templates/isis/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
    <link rel="stylesheet" type="text/css" href="<?php echo JURI::root(); ?>administrator/components/com_joomailermailchimpintegration/installer/css/bootstrap.min.css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="<?php echo JURI::root(); ?>administrator/components/com_joomailermailchimpintegration/installer/css/installer.css" media="screen"/>
    <script src="<?php echo JURI::root(); ?>administrator/components/com_joomailermailchimpintegration/installer/js/jquery.1.9.1.min.js" type="text/javascript"></script>
    <script src="<?php echo JURI::root(); ?>administrator/components/com_joomailermailchimpintegration/installer/js/bootstrap.min.js" type="text/javascript"></script>
</head>
<body>
<div class="container">
    <div class="box">
        <h2 class="text-center">
            <img src="<?php echo JURI::root(); ?>media/com_joomailermailchimpintegration/backend/images/logo.png" alt="" />
        </h2>

        <hr/>

        <div class="row bs-wizard text-center" style="border-bottom:0;">
            <div class="col-xs-2 bs-wizard-step active" id="step1">
                <div class="text-center bs-wizard-stepnum">Unpacking Files</div>
                <div class="progress">
                    <div class="progress-bar"></div>
                </div>
                <a href="#" class="bs-wizard-dot"></a>
            </div>

            <div class="col-xs-2 bs-wizard-step disabled" id="step2">
                <div class="text-center bs-wizard-stepnum">Updating Database</div>
                <div class="progress">
                    <div class="progress-bar"></div>
                </div>
                <a href="#" class="bs-wizard-dot"></a>
            </div>

            <div class="col-xs-2 bs-wizard-step disabled" id="step3">
                <div class="text-center bs-wizard-stepnum">Installing Extensions</div>
                <div class="progress">
                    <div class="progress-bar"></div>
                </div>
                <a href="#" class="bs-wizard-dot"></a>
            </div>

            <div class="col-xs-2 bs-wizard-step disabled" id="step4">
                <div class="text-center bs-wizard-stepnum">Migration</div>
                <div class="progress">
                    <div class="progress-bar"></div>
                </div>
                <a href="#" class="bs-wizard-dot"></a>
            </div>

            <div class="col-xs-2 bs-wizard-step disabled" id="step5">
                <div class="text-center bs-wizard-stepnum">Cleanup</div>
                <div class="progress">
                    <div class="progress-bar"></div>
                </div>
                <a href="#" class="bs-wizard-dot"></a>
            </div>

            <div class="col-xs-2 bs-wizard-step disabled" id="step6">
                <div class="text-center bs-wizard-stepnum">Done!</div>
                <div class="progress">
                    <div class="progress-bar"></div>
                </div>
                <a href="#" class="bs-wizard-dot"></a>
            </div>
        </div>

        <hr/>

        <div id="stepsProgresses"></div>

        <div id="loader">
            <img src="<?php echo JURI::root(); ?>media/com_joomailermailchimpintegration/backend/images/loader_32.gif" alt="" />
        </div>
    </div>
</div>

<div class="modal fade" id="errorReportModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="pull-right">
                    <button type="button" class="btn btn-default cancel" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary submit" style="height: 34px;width: 59px;">Send</button>
                </div>
                <h4 class="modal-title" id="myModalLabel">Send error report</h4>
            </div>
            <div class="modal-body">
                <form id="errorReportForm">
                    The following report will be sent to errors@joomlamailer.com<br/><br/>

                    <div class="box">
                        <div class="pull-right clearfix"><?php echo date('Y-m-d H:i:s'); ?></div>
                        Installation error on: <?php echo JURI::root(); ?><br/><br/>
                        <ul>
                            <li>
                                joomlamailer: <?php echo $manifest->version . ' (' . $manifest->creationDate . ')'; ?></li>
                            <li><?php $jversion = new JVersion();
                                echo $jversion->getLongVersion(); ?></li>
                            <li>PHP: <?php echo phpversion(); ?></li>
                            <li>Database: <?php echo JFactory::getDBO()->name
                                    . ' (' . JFactory::getDBO()->getConnection()->server_info . ')'; ?></li>
                        </ul>
                        <label for="contact">Contact (optional)</label><br/>

                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1">@</span>
                            <input type="text" class="form-control" aria-describedby="basic-addon1" id="contact" name="contact" value="<?php echo JFactory::getConfig()->get('mailfrom'); ?>" />
                        </div>
                        <br/>
                        <label for="notes">Additional information (optional)</label><br/>
                        <textarea id="notes" name="notes" class="form-control" placeholder="Please enter here any additional information, which might help us to track down the problem."></textarea>
                        <br/>
                        The following error(s) occurred during the installation:
                        <br/><br/>
                        <pre id="errorReportPrintErrors"></pre>
                    </div>
                    <input type="hidden" name="domain" value="<?php echo JURI::root(); ?>"/>
                    <input type="hidden" name="joomlamailer" value="<?php echo $manifest->version
                        . ' (' . $manifest->creationDate . ')'; ?>"/>
                    <input type="hidden" name="Joomla" value="<?php echo $jversion->getLongVersion(); ?>"/>
                    <input type="hidden" name="PHP" value="<?php echo phpversion(); ?>"/>
                    <input type="hidden" name="Database" value="<?php echo JFactory::getDBO()->name
                        . ' (' . JFactory::getDBO()->getConnection()->server_info . ')'; ?>"/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary submit">Send</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    !function ($) {
        var adminUrl = '<?php echo JURI::base();?>';
        var errors = {};
        var steps = {
            1: {
                'label': 'Preparing installation'
            },
            2: {
                'label': 'Creating database tables'
            },
            3: {
                'label': 'Installing additional extensions'
            },
            4: {
                'label': 'Migrate settings and uninstall obsolete extensions'
            },
            5: {
                'label': 'Removing temporary installation files'
            }
        };

        var dbTables = [
            'List subscribers',
            'Custom fields',
            'Campaigns',
            'Registration data',
            'Miscellaneous data',
            'CRM data',
            'CRM users',
            'Update existing tables'
        ];

        var extensions = [
            'User Plugin',
            'Signup Module',
            'Admin Statistics Module',
            'Content Plugin: Joomla Core',
            'Content Plugin: K2',
            'Content Plugin: Virtuemart',
            'Content Plugin: Table Of Contents',
            'Content Plugin: Sidebar Editor',
            'Content Plugin: Facebook Icon',
            'Content Plugin: Twitter Icon',
            'Content Plugin: Instagram Icon',
            'Content Plugin: Myspace Icon',
            'Content Plugin: JomSocial Discussions',
            'Content Plugin: JomSocial Profiles',
            'JomSocial Plugin'
        ];

        var migration = [
            'System plugin migration',
            'Uninstall system plugin',
            'Uninstall signup component',
            'Uninstall Community Builder plugin',
            'Signup module migration'
        ];

        var TemplateEngine = function (html, options) {
            var re = /<#([^#>]+)?#>/g, reExp = /(^( )?(if|for|else|switch|case|break|{|}))(.*)?/g, code = 'var r=[];\n', cursor = 0;
            var add = function (line, js) {
                js ? (code += line.match(reExp) ? line + '\n' : 'r.push(' + line + ');\n') :
                    (code += line != '' ? 'r.push("' + line.replace(/"/g, '\\"') + '");\n' : '');
                return add;
            }
            while (match = re.exec(html)) {
                add(html.slice(cursor, match.index))(match[1], true);
                cursor = match.index + match[0].length;
            }
            add(html.substr(cursor, html.length - cursor));
            code += 'return r.join("");';
            return new Function(code.replace(/[\r\t\n]/g, '')).apply(options);
        }
        var progressContainerTpl = '<div class="box">\
                    <h3><#this.label#></h3>\
                    <div class="progress progress-striped active" id="progress_step_<#this.step#>>">\
                        <div class="progress-bar" style="width: 0%;"></div>\
                    </div>\
                </div>';
        var tableRowTpl = '<tr id="<#this.id#><#this.step#>">\
                    <td><#this.label#></td>\
                    <td class="clearfix" style="width: 20px;">\
                        <div class="pull-right">\
                            <span class="label label-default">Waiting</span>\
                        </div>\
                    </td>\
                </tr>';

        var installationSteps = {
            1: function () {
                installerRunning = true;
                var pc = TemplateEngine(progressContainerTpl, {step: 1, label: steps[1].label});
                $(pc).prependTo('#stepsProgresses').hide();

                setTimeout(function () {
                    $('#stepsProgresses .box:first-of-type').slideDown().queue(function () {
                        var _this = $(this);
                        $.ajax({
                            url: adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=installer&format=raw&task=prepare',
                            beforeSend: function () {
                                _this.find('.progress-bar').css('width', '33%');
                            }
                        }).done(function () {
                            _this.find('.progress-bar').css('width', '100%');
                            $('#step1').removeClass('active').addClass('complete');

                            setTimeout(function () {
                                _this.find('.progress').removeClass('progress-striped').find('.progress-bar').addClass('progress-bar-success');
                                $('#step2').removeClass('disabled').addClass('active');

                                setTimeout(function () {
                                    installationSteps[2]();
                                }, 500);
                            }, 500);
                        });
                    });
                    $('#loader').slideUp();
                }, 500);
            },
            2: function () {
                var pc = TemplateEngine(progressContainerTpl, {step: 2, label: steps[2].label});
                $(pc).prependTo('#stepsProgresses').hide();

                var table = '<table class="table"><tbody>';
                for (var i = 0; i < dbTables.length; i++) {
                    table += TemplateEngine(tableRowTpl, {
                        id: 'updateDbStep_',
                        step: (i + 1),
                        label: dbTables[i]
                    });
                }
                table += '</tbody></table>';
                $(table).appendTo('#stepsProgresses .box:first-of-type');

                $('#stepsProgresses .box:first-of-type').slideDown().queue(function () {
                    $(this).find('.progress-bar').css('width', Math.ceil(100 / dbTables.length) + '%');
                });

                var d1 = new $.Deferred();
                updateDb(1, d1);
                d1.done(function () {
                    $('#step2').removeClass('active').addClass('complete');
                    setTimeout(function () {
                        $('#step3').removeClass('disabled').addClass('active');
                        var status = (errors.updateDb !== undefined) ? 'danger' : 'success';
                        $('#stepsProgresses .box:first-of-type').find('.progress').removeClass('progress-striped')
                            .find('.progress-bar').addClass('progress-bar-' + status);
                        setTimeout(function () {
                            installationSteps[3]();
                        }, 500);
                    }, 500);
                });
            },
            3: function () {
                var pc = TemplateEngine(progressContainerTpl, {step: 3, label: steps[3].label});
                $(pc).prependTo('#stepsProgresses').hide();

                var table = '<table class="table"><tbody>';
                for (var i = 0; i < extensions.length; i++) {
                    table += TemplateEngine(tableRowTpl, {
                        id: 'installExtensionsStep_',
                        step: (i + 1),
                        label: extensions[i]
                    });
                }
                table += '</tbody></table>';
                $(table).appendTo('#stepsProgresses .box:first-of-type');

                $('#stepsProgresses .box:first-of-type').slideDown().queue(function () {
                    $(this).find('.progress-bar').css('width', Math.ceil(100 / extensions.length) + '%');
                });

                var d2 = new $.Deferred();
                installExtensions(1, d2);
                d2.done(function () {
                    setTimeout(function () {
                        $('#step3').removeClass('active').addClass('complete');
                        var status = (errors.installExtensions !== undefined) ? 'danger' : 'success';
                        $('#stepsProgresses .box:first-of-type').find('.progress').removeClass('progress-striped')
                            .find('.progress-bar').addClass('progress-bar-' + status);
                        setTimeout(function () {
                            $('#step4').removeClass('disabled').addClass('active');
                            setTimeout(function () {
                                installationSteps[4]();
                            }, 500);
                        }, 500);
                    }, 2000);
                });
            },
            4: function() {
                var pc = TemplateEngine(progressContainerTpl, {step: 4, label: steps[4].label});
                $(pc).prependTo('#stepsProgresses').hide().slideDown();

                var table = '<table class="table"><tbody>';
                for (var i = 0; i < migration.length; i++) {
                    table += TemplateEngine(tableRowTpl, {
                        id: 'migrateStep_',
                        step: (i + 1),
                        label: migration[i]
                    });
                }
                table += '</tbody></table>';
                $(table).appendTo('#stepsProgresses .box:first-of-type');

                $('#stepsProgresses .box:first-of-type').slideDown().queue(function () {
                    $(this).find('.progress-bar').css('width', Math.ceil(100 / migration.length) + '%');
                });

                var d3 = new $.Deferred();
                migrate(1, d3);
                d3.done(function () {
                    setTimeout(function () {
                        $('#step4').removeClass('active').addClass('complete');
                        var status = (errors.migration !== undefined) ? 'danger' : 'success';
                        $('#stepsProgresses .box:first-of-type').find('.progress').removeClass('progress-striped')
                            .find('.progress-bar').addClass('progress-bar-' + status);
                        setTimeout(function () {
                            $('#step5').removeClass('disabled').addClass('active');
                            setTimeout(function () {
                                installationSteps[5]();
                            }, 500);
                        }, 500);
                    }, 2000);
                });
            },
            5: function () {
                var pc = TemplateEngine(progressContainerTpl, {step: 5, label: steps[5].label});
                $(pc).prependTo('#stepsProgresses').hide().slideDown();

                $.ajax({
                    url: adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=installer&format=raw&task=cleanup',
                    beforeSend: function () {
                        $('#stepsProgresses .box:first-of-type').find('.progress-bar').css('width', '33%');
                    }
                }).done(function () {
                    $('#stepsProgresses .box:first-of-type').find('.progress-bar').css('width', '100%');
                    setTimeout(function () {
                        $('#step5').removeClass('active').addClass('complete');
                        $('#stepsProgresses .box:first-of-type').find('.progress').removeClass('progress-striped')
                            .find('.progress-bar').addClass('progress-bar-success');
                        setTimeout(function () {
                            $('#step6').removeClass('disabled').addClass('active');

                            installerRunning = false;

                            if (Object.keys(errors).length == 0) {
                                $('<div class="alert alert-success" role="alert">\
                                        <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>\
                                        <b>Congratulations! You have successfully installed the joomlamailer suite.</b>\
                                        Please click this button to proceed to the component:\
                                        <a href="index.php?option=com_joomailermailchimpintegration" class="btn btn-success">\
                                        joomlamailer dashboard</a>\
                                        </div>').prependTo('#stepsProgresses').hide().slideDown();
                            } else {
                                var errorString = JSON.stringify(errors, undefined, 2);
                                errorString = errorString.replace(/\\r\\n|\\n/g, '').replace(/ +/g, ' ');
                                $('#errorReportPrintErrors').html(errorString);

                                $('<div class="alert alert-warning" role="alert">\
                                        <span class="glyphicon glyphicon-thumbs-down" aria-hidden="true"></span>\
                                        <b>Unfortunately the installation finished with errors.</b><br />\
                                        You should try to install the component once again. If the problem remains please\
                                        click this button to send an error report:\
                                        <span class="btn btn-warning btn-sm" data-toggle="modal" data-target="#errorReportModal">\
                                        Send error report</span>\
                                        <a href="index.php" class="btn btn-info btn-sm pull-right">Back to Joomla!</a>\
                                        </div>').prependTo('#stepsProgresses').hide().slideDown();
                            }
                        }, 500);
                    }, 2000);
                });
            }
        }

        function updateDb(step, d1) {
            $.ajax({
                url: adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=installer&format=raw&task=updatedb',
                data: {
                    step: step
                },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        if (errors.updateDb === undefined) {
                            errors.updateDb = [];
                        }
                        if (typeof response.error === 'string') {
                            var errorLabel = 'An error';
                            var errorMsg = response.error;
                        } else {
                            var errorLabel = 'Some errors';
                            var errorMsg = '';
                            for (var i in response.error) {
                                errorMsg += response.error[i].error + '<br /><br />';
                            }
                        }
                        errors.updateDb.push(response);
                        $('#updateDbStep_' + step + ' .label').removeClass('label-default').addClass('label-danger').text('Error');
                        $('#updateDbStep_' + step + ' td:first-of-type').append('<div class="alert alert-danger" role="alert">'
                        + ' <b>' + errorLabel + ' occurred!</b><br />' + errorMsg + '</div>');
                    } else {
                        $('#updateDbStep_' + step + ' .label').removeClass('label-default').addClass('label-success').text('Done');
                    }
                }
            }).done(function () {
                var width = (step < dbTables.length) ? Math.ceil(100 / dbTables.length) * step : 100;
                $('#stepsProgresses .box:first-of-type').find('.progress-bar').css('width', width + '%');

                if (step < dbTables.length) {
                    setTimeout(function () {
                        updateDb(++step, d1);
                    }, 350);
                } else {
                    d1.resolve();
                }
            });
        }

        function installExtensions(step, d2) {
            $.ajax({
                url: adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=installer&format=raw&task=installext',
                data: {
                    step: step
                },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        if (errors.installExtensions === undefined) {
                            errors.installExtensions = [];
                        }
                        errors.installExtensions.push(response);
                        $('#installExtensionsStep_' + step + ' .label').removeClass('label-default').addClass('label-danger').text('Error');
                        $('#installExtensionsStep_' + step + ' td:first-of-type').append('<div class="alert alert-danger" role="alert">'
                        + ' <b>An error occurred!</b><br />' + response.error
                        + '</div>');
                    } else if (response.notification) {
                        var text = (response.label) ? response.label : 'Warning';
                        $('#installExtensionsStep_' + step + ' .label').removeClass('label-default').addClass('label-warning').text(text);
                        $('#installExtensionsStep_' + step + ' td:first-of-type').append('<div class="alert alert-warning" role="alert">'
                        + response.notification + '</div>');
                    } else {
                        var text = (response.notRequired) ? 'Not required' : 'Done';
                        $('#installExtensionsStep_' + step + ' .label').removeClass('label-default').addClass('label-success').text(text);
                    }
                }
            }).done(function () {
                var width = (step < extensions.length) ? Math.ceil(100 / extensions.length) * step : 100;
                $('#stepsProgresses .box:first-of-type').find('.progress-bar').css('width', width + '%');

                if (step < extensions.length) {
                    setTimeout(function () {
                        installExtensions(++step, d2);
                    }, 350);
                } else {
                    d2.resolve();
                }
            });
        }

        function migrate(step, d3) {
            $.ajax({
                url: adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=installer&format=raw&task=migrate',
                data: {
                    step: step
                },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        if (errors.migration === undefined) {
                            errors.migration = [];
                        }
                        errors.migration.push(response);
                        $('#migrateStep_' + step + ' .label').removeClass('label-default').addClass('label-danger').text('Error');
                        $('#migrateStep_' + step + ' td:first-of-type').append('<div class="alert alert-danger" role="alert">'
                        + ' <b>An error occurred!</b><br />' + response.error
                        + '</div>');
                    } else if (response.notification) {
                        var text = (response.label) ? response.label : 'Warning';
                        $('#migrateStep_' + step + ' .label').removeClass('label-default').addClass('label-warning').text(text);
                        $('#migrateStep_' + step + ' td:first-of-type').append('<div class="alert alert-warning" role="alert">'
                        + response.notification + '</div>');
                    } else {
                        var text = (response.notRequired) ? 'Not required' : 'Done';
                        $('#migrateStep_' + step + ' .label').removeClass('label-default').addClass('label-success').text(text);
                    }
                }
            }).done(function () {
                var width = (step < migration.length) ? Math.ceil(100 / migration.length) * step : 100;
                $('#stepsProgresses .box:first-of-type').find('.progress-bar').css('width', width + '%');

                if (step < migration.length) {
                    setTimeout(function () {
                        migrate(++step, d3);
                    }, 350);
                } else {
                    d3.resolve();
                }
            });
        }

        var installerRunning = false;

        $(document).ready(function () {
            installationSteps[1]();

            $(document).on('click', '#errorReportModal button.submit', function () {
                $.ajax({
                    url: adminUrl + 'index.php?option=com_joomailermailchimpintegration&controller=installer&format=raw&task=sendreport',
                    type: 'post',
                    beforeSend: function () {
                        $('#errorReportModal button.submit').html('<img src="<?php echo JURI::root(); ?>media/com_joomailermailchimpintegration/backend/images/loader_16_blue.gif" alt="" />');
                    },
                    data: {
                        formData: $('#errorReportForm').serialize(),
                        errors: JSON.stringify(errors)
                    },
                    success: function (response) {
                        if (response) {
                            alert(response);
                        } else {
                            alert('The report was sent successfully. We will look into it as soon as possible. '
                            + 'Please note that we can not reply to all error reports. If you have further '
                            + 'questions please use our forums on joomlamailer.com Thank you!');
                        }
                    }
                }).done(function () {
                    $('#errorReportModal button.submit').html('Send');
                    $('#errorReportModal').modal('hide');
                });
            });
            $(document).on('click', '#errorReportModal button.cancel', function () {
                $('#errorReportModal button.submit').html('Send');
            });
        });

        window.addEventListener('beforeunload', function (e) {
            if (installerRunning !== true) {
                return undefined;
            }

            var confirmationMessage = 'The installer did not finish!';
            (e || window.event).returnValue = confirmationMessage; //Gecko + IE
            return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
        });

    }(window.jQuery);
</script>
</body>
</html>
