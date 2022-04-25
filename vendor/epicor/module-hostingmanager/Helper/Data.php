<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Helper;


use Epicor\Common\Helper\Context;

class Data extends \Epicor\Common\Helper\Data
{

    protected $_prefix;
    protected $_site_folder;
    protected $_cert_folder;
    protected $directory_list;

    /**
     *
     * @var \Magento\Framework\Filesystem\Io\File 
     */
    protected $_file;
    protected $_vhost_template = 'server {

	listen 80;

	server_name www.jimbo.co.uk;

	set $MAGE_ROOT "/var/www/";
	access_log /var/log/nginx/SITEDIRECTORY.access.log;
	error_log /var/log/nginx/SITEDIRECTORY.error.log;

	root $MAGE_ROOT/pub;

	index index.php;
	autoindex off;
	charset UTF-8;
	error_page 404 403 = /errors/404.php;
	#add_header "X-UA-Compatible" "IE=Edge";

	# PHP entry point for setup application
	location ~* ^/setup($|/) {

		root $MAGE_ROOT;
		location ~ ^/setup/index.php {

			fastcgi_pass unix:/var/run/[php_version]-fpm_[base_folder].socket;
			fastcgi_index index.php;
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
			include fastcgi_params;
		}

		location ~ ^/setup/(?!pub/). {

			deny all;
		}

		location ~ ^/setup/pub/ {

			add_header X-Frame-Options "SAMEORIGIN";
		}
	}


	# PHP entry point for update application
	location ~* ^/update($|/) {

		root $MAGE_ROOT;

		location ~ ^/update/index.php {

			fastcgi_split_path_info ^(/update/index.php)(/.+)$;
			fastcgi_pass unix:/var/run/[php_version]-fpm_[base_folder].socket;
			fastcgi_index index.php;
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
			fastcgi_param PATH_INFO $fastcgi_path_info;
			include fastcgi_params;
		}

		# Deny everything but index.php
		location ~ ^/update/(?!pub/). {

			deny all;
		}

		location ~ ^/update/pub/ {

			add_header X-Frame-Options "SAMEORIGIN";
		}
	}


	location / {

		try_files $uri $uri/ /index.php?$args;
	}

	location /pub/ {

		location ~ ^/pub/media/(downloadable|customer|import|theme_customization/.*\.xml) {

			deny all;
		}
		alias $MAGE_ROOT/pub/;
		add_header X-Frame-Options "SAMEORIGIN";
	}


	location /static/ {

		# Uncomment the following line in production mode
		# expires max;

		# Remove signature of the static files that is used to overcome the browser cache
		location ~ ^/static/version {

			rewrite ^/static/(version\d*/)?(.*)$ /static/$2 last;
		}

		location ~* \.(ico|jpg|jpeg|png|gif|svg|js|css|swf|eot|ttf|otf|woff|woff2)$ {

			add_header Cache-Control "public";
			add_header X-Frame-Options "SAMEORIGIN";
			expires +1y;

			if (!-f $request_filename) {

				rewrite ^/static/(version\d*/)?(.*)$ /static.php?resource=$2 last;
			}
		}
		location ~* \.(zip|gz|gzip|bz2|csv|xml)$ {

			add_header Cache-Control "no-store";
			add_header X-Frame-Options "SAMEORIGIN";
			expires off;

			if (!-f $request_filename) {

				rewrite ^/static/(version\d*/)?(.*)$ /static.php?resource=$2 last;
			}
		}
		if (!-f $request_filename) {

			rewrite ^/static/(version\d*/)?(.*)$ /static.php?resource=$2 last;
		}
		add_header X-Frame-Options "SAMEORIGIN";
	}

	location /media/ {

		try_files $uri $uri/ /get.php?$args;

		location ~ ^/media/theme_customization/.*\.xml {

			deny all;
		}

		location ~* \.(ico|jpg|jpeg|png|gif|svg|js|css|swf|eot|ttf|otf|woff|woff2)$ {

			add_header Cache-Control "public";
			add_header X-Frame-Options "SAMEORIGIN";
			expires +1y;
			try_files $uri $uri/ /get.php?$args;
		}
		location ~* \.(zip|gz|gzip|bz2|csv|xml)$ {

			add_header Cache-Control "no-store";
			add_header X-Frame-Options "SAMEORIGIN";
			expires off;
			try_files $uri $uri/ /get.php?$args;
		}
		add_header X-Frame-Options "SAMEORIGIN";
	}

	location /media/customer/ {

		deny all;
	}

	location /media/downloadable/ {

		deny all;
	}

	location /media/import/ {

		deny all;
	}

	# PHP entry point for main application
	#location ~ (index|get|static|report|404|503)\.php$ {
	location ~* \.php$ {

		try_files $uri =404;
		fastcgi_pass unix:/var/run/[php_version]-fpm_[base_folder].socket;
		fastcgi_buffers 1024 4k;

		fastcgi_read_timeout 600s;
		fastcgi_connect_timeout 600s;

		fastcgi_index index.php;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		fastcgi_param MAGE_RUN_TYPE store;
		fastcgi_param MAGE_RUN_CODE default;
		include fastcgi_params;
	}

	gzip on;
	gzip_disable "msie6";

	gzip_comp_level 6;
	gzip_min_length 1100;
	gzip_buffers 16 8k;
	gzip_proxied any;
	gzip_types
	text/plain
	text/css
	text/js
	text/xml
	text/javascript
	application/javascript
	application/x-javascript
	application/json
	application/xml
	application/xml+rss
	image/svg+xml;
	gzip_vary on;

	# Banned locations (only reached if the earlier PHP entry point regexes dont match)
	location ~* (\.php$|\.htaccess$|\.git) {

		deny all;
	}

}    
';

    public function __construct(Context $context)
    {
       $this->directory_list = $context->getDirectoryList();
       $this->_cert_folder = $this->directory_list->getRoot() . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'ssl';
       $this->_site_folder = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'vhost';
       parent::__construct($context); 
    }


    public function getTempFileName()
    {

        $randomString = md5(microtime(true));
        $this->getFileIO();
        return $this->_cert_folder . DIRECTORY_SEPARATOR . $this->getFilePrefix() . '-' . $randomString;
    }

    /**
     * 
     * @return \Magento\Framework\Filesystem\Io\File
     */
    protected function getFileIO()
    {

        if (!$this->_file) {
            $this->_file = $this->ioFileFactory->create();
            $this->_file->open();
            $this->_file->checkAndCreateFolder($this->_cert_folder);
            $this->_file->checkAndCreateFolder($this->_site_folder);
        }
        return $this->_file;
    }

    protected function getCertificateFileName($cert)
    {
        return $this->_cert_folder . DIRECTORY_SEPARATOR . $this->getFilePrefix() . '-' . $cert->getId();
    }

    protected function getSiteFileName($site)
    {
        return $this->_site_folder . DIRECTORY_SEPARATOR . $this->getFilePrefix() . '-' . $site->getId();
    }

    public function deleteCertificateFiles($cert)
    {
        $file = $this->getFileIO();
        $filename = $this->getCertificateFileName($cert);

        $file->rm($filename . '.key');
        $file->rm($filename . '.crt');
    }

    public function deleteTempFiles($filename)
    {
        $file = $this->getFileIO();

        $file->rm($filename . '.key');
        $file->rm($filename . '.csr');
        $file->rm($filename . '.config');
    }

    public function createCsrConfigFile($filename, $config, $key_size, $digest_alg)
    {
        $data = '[ req ]
                prompt = no
                encrypt_key = no
                default_md = ' . $digest_alg . '
                default_bits = ' . $key_size . '
                distinguished_name = req_distinguished_name

                [ req_distinguished_name ]
                C=' . $config['C'] . '
                ST=' . $config['ST'] . '
                L=' . $config['L'] . '
                O=' . $config['O'] . '
                OU=' . $config['OU'] . '
                CN=' . $config['CN'] . '
                emailAddress=' . $config['E'] . '         
                ';
        $file = $this->getFileIO();
        $file->write($filename, $data);
    }

    /**
     * Save Private Key data
     * 
     * @param \Epicor\HostingManager\Model\Certificate $cert
     */
    public function savePrivateKey($cert)
    {

        $file = $this->getFileIO();
        $filename = $this->getCertificateFileName($cert) . '.key';

        $data = trim($cert->getPrivateKey());
        $file->write($filename, $data);
    }

    /**
     * Save Certificate with CA Certificate data
     * 
     * @param \Epicor\HostingManager\Model\Certificate $cert
     */
    public function saveCertificate($cert)
    {

        $file = $this->getFileIO();
        $filename = $this->getCertificateFileName($cert) . '.crt';

        $data = trim($cert->getCertificate()) . "\n" . trim($cert->getCACertificate());
        $file->write($filename, $data);
    }

    /**
     * 
     * @param \Epicor\HostingManager\Model\Site $site
     */
    public function saveSiteFile($site)
    {
        $file     = $this->getFileIO();
        $filename = $this->getSiteFileName($site);
        $url      = $site->getUrl();
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$root = Mage::getBaseDir();
        $root     = $this->directoryList->getRoot();
        //M1 > M2 Translation End
        
        //hosting interface to allow extra domains to be added for a site
        $serverName = $site->getExtraDomains();
        if ($serverName) {
            //Remove comma's
            $removeCommaServer = preg_replace('/[,]/', ' ', $serverName);
            $url               = $site->getUrl() . " " . $removeCommaServer;
        } else {
            $url = $site->getUrl();
        }
        
        if ($file->fileExists($filename)) {
            $data = $file->read($filename);
        } else {
            $data = $this->_vhost_template;
            
            $path_parts  = explode(DIRECTORY_SEPARATOR, $root);
            $base_folder = $path_parts[count($path_parts) - 1] != 'live' ? 'test' : 'live';
            $php =  explode('.', phpversion());
            $php_version = 'php'.$php[0].'.'.$php[1];
            $data        = str_replace('[php_version]', $php_version, $data);
            $data        = str_replace('[base_folder]', $base_folder, $data);
        }
        
        
        $data      = preg_replace('/(server_name)[\s]+.*;/', '$1' . "\t\t" . $url . ';', $data);
        $data      = preg_replace('/(set[\s]+\$MAGE_ROOT[\s]+").*(";)/', '$1' . $root . '$2', $data);
        $site_name = $this->getFilePrefix() . '-' . $site->getId();
        $data      = preg_replace('/(access_log[\s]+\/var\/log\/nginx\/).+(.access.log;)/', '$1' . $site_name . '$2', $data);
        $data      = preg_replace('/(error_log[\s]+\/var\/log\/nginx\/).+(.error.log;)/', '$1' . $site_name . '$2', $data);
        
        $type = $site->getIsWebsite() ? 'website' : 'store';
        $code = $site->getCode();
        if (empty($code)) {
            $pattern = '/[\s]+fastcgi_param[\s]+MAGE_RUN_CODE[\s]+.*;/';
            if (preg_match($pattern, $data)) {
                $data = preg_replace($pattern, '', $data);
            }
        } else {
            
            $pattern = '/fastcgi_param[\s]+MAGE_RUN_CODE[\s]+.+;/';
            if (preg_match($pattern, $data)) {
                $data = preg_replace('/(fastcgi_param[\s]+MAGE_RUN_CODE[\s]+).+;/', '$1' . $code . ';', $data);
            } else {
                $data = preg_replace('/(fastcgi_param[\s]+MAGE_RUN_TYPE[\s]+.+;)/', '$1' . "\n\t\tfastcgi_param MAGE_RUN_CODE " . $code . ';', $data);
            }
        }
        $data = preg_replace('/(fastcgi_param[\s]+MAGE_RUN_TYPE[\s]+).+;/', '$1' . $type . ';', $data);
        
        
        $cert = $site->getCertificate();
        if ($cert && $cert->getId() && $cert->isValidCertificate()) {
            $cert         = $site->getCertificate();
            $certFilename = $this->getCertificateFileName($cert);
            
            $key = $certFilename . '.key';
            $crt = $certFilename . '.crt';
            if (preg_match('/ssl_certificate[\s]+.*;/', $data)) {
                $data = preg_replace('/(ssl_certificate[\s]+).*;/', '$1' . $crt . ';', $data);
            } else {
                $data = preg_replace('/(server_name[\s]+.*;)/', '$1' . "\n\n\tssl_certificate\t\t" . $crt . ';', $data);
            }
            
            if (preg_match('/ssl_certificate_key[\s]+.*;/', $data)) {
                $data = preg_replace('/(ssl_certificate_key[\s]+).*;/', '$1' . $key . ';', $data);
            } else {
                $pattern = '/(server_name[\s]+.*;)/';
                if (preg_match('/ssl_certificate[\s]+.*;/', $data)) {
                    $pattern = '/(ssl_certificate[\s]+.*;)/';
                }
                $data = preg_replace($pattern, '$1' . "\n\tssl_certificate_key\t" . $key . ';', $data);
            }
            
            if (!preg_match('/listen[\s]+443[\s]+ssl;/', $data)) {
                $data = preg_replace('/(listen[\s]+80;).*/', '$1' . "\n\tlisten\t443 ssl;", $data);
            }
            
            $sslProtocols = "TLSv1 TLSv1.1 TLSv1.2";
            if (preg_match('/ssl_protocols[\s]+.*;/', $data)) {
                $data = preg_replace('/(ssl_protocols[\s]+).*;/', '$1' . $sslProtocols . ';', $data);
            } else {
                $data = preg_replace('/(ssl_certificate_key[\s]+.*;)/', '$1' . "\n\n\tssl_protocols\t\t" . $sslProtocols . ';', $data);
            }
        } else {
            
            $pattern = '/[\s]+listen[\s]+443[\s]+ssl;[.]*/';
            if (preg_match($pattern, $data)) {
                $data = preg_replace($pattern, '', $data);
            }
            
            $pattern = '/[\s]+ssl_certificate[\s]+.*;.*/';
            if (preg_match($pattern, $data)) {
                $data = preg_replace($pattern, '', $data);
            }
            
            $pattern = '/[\s]+ssl_certificate_key[\s]+.*;.*/';
            if (preg_match($pattern, $data)) {
                $data = preg_replace($pattern, '', $data);
            }
            
            $pattern = '/[\s]+ssl_protocols[\s]+.*;.*/';
            if (preg_match($pattern, $data)) {
                $data = preg_replace($pattern, '', $data);
            }
        }
        $data = preg_replace("/\r/", "", $data);
        
        $this->updateStoreConfig($site);
        $file->write($filename, $data);
        if (!$this->scopeConfig->isSetFlag('epicor_hosting/vhost/initial', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $site->getIsDefault()) {
            $path_parts = explode(DIRECTORY_SEPARATOR, $root);
            $vhost_file = $this->_site_folder . DIRECTORY_SEPARATOR . $path_parts[count($path_parts) - 1];
            if ($file->fileExists($vhost_file)) {
                $file->rm($vhost_file);
                
                //M1 > M2 Translation Begin (Rule P2-2)
                //$config = Mage::getConfig();
                $config = $this->resourceConfig;
                //M1 > M2 Translation End
                
                $config->saveConfig('epicor_hosting/vhost/initial', 1, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                //$config->cleanCache();
            }
        }
    }

    /**
     * 
     * @param \Epicor\HostingManager\Model\Site $site
     */
    public function updateStoreConfig($site)
    {
        
        //M1 > M2 Translation Begin (Rule P2-2)
        //$config = Mage::getConfig();
        $config = $this->resourceConfig;
        //M1 > M2 Translation End
        
        $secure           = 'web/secure/base_url';
        $unsecure         = 'web/unsecure/base_url';
        $cookie           = 'web/cookie/cookie_domain';
        $baseLinkUnsecure = 'web/unsecure/base_link_url';
        $baseLinkSecure   = 'web/secure/base_link_url';
        
        if (!$site->getIsDefault()) {
            $scope   = $site->getOrigData('is_website') ? 'websites' : 'stores';
            $scopeId = $site->getOrigData('child_id');
            
            //clean old store url;
            $config->deleteConfig($unsecure, $scope, $scopeId);
            $config->deleteConfig($secure, $scope, $scopeId);
            $config->deleteConfig($cookie, $scope, $scopeId);
            $config->deleteConfig($baseLinkUnsecure, $scope, $scopeId);
            $config->deleteConfig($baseLinkSecure, $scope, $scopeId);
        }
        //set new url
        $url        = 'http://' . $site->getUrl() . '/';
        $secure_url = $url;
        if ($site->getCertificate() && $site->getCertificate()->getId() && $site->getCertificate()->isValidCertificate())
            $secure_url = 'https://' . $site->getUrl() . '/';
        
        if ($site->getIsDefault()) {
            $scope   = 'default';
            $scopeId = 0;
        } else {
            $scope   = $site->getIsWebsite() ? 'websites' : 'stores';
            $scopeId = $site->getChildId();
        }
        $config->saveConfig($secure, $secure_url, $scope, $scopeId);
        $config->saveConfig($unsecure, $url, $scope, $scopeId);
        $config->saveConfig($cookie, $site->getUrl(), $scope, $scopeId);
        $config->saveConfig($baseLinkUnsecure, $url, $scope, $scopeId);
        $config->saveConfig($baseLinkSecure, $secure_url, $scope, $scopeId);
        
        //$config->cleanCache();
    }

    /**
     * 
     * @param \Epicor\HostingManager\Model\Site $site
     */
    public function deleteSiteFile($site)
    {
        $file = $this->getFileIO();
        $filename = $this->getSiteFileName($site);

        $file->rm($filename);
    }

    public function getFilePrefix()
    {
        if (!$this->_prefix) {
            $this->_prefix = basename($this->directory_list->getRoot());
            if (!$this->_prefix) {
                $this->_prefix = basename($this->directory_list->getRoot());
                //M1 > M2 Translation Begin (Rule P2-2)
                //Mage::getConfig()->init()->saveConfig('epicor_hosting/file/prefix', $this->_prefix);
                $this->resourceConfig->saveConfig('epicor_hosting/file/prefix', $this->_prefix, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                //M1 > M2 Translation End
                
            }
        }
        return $this->_prefix;
    }

    /**
     * hosting manager to set the unsecure base url to https:
     * @param \Epicor\HostingManager\Model\Site $site
     */
    public function setUnsecureHttps($site)
    {
        //M1 > M2 Translation Begin (Rule P2-2)
        //$config = Mage::getConfig();
        $config = $this->resourceConfig;
        //M1 > M2 Translation End
        
        $unsecure         = 'web/unsecure/base_url';
        $baseLinkUnsecure = 'web/unsecure/base_link_url';
        if ($site->getSecure()) {
            $url = 'https://' . $site->getUrl() . '/';
        } else {
            $url = 'http://' . $site->getUrl() . '/';
        }
        if ($site->getIsDefault()) {
            $scope   = 'default';
            $scopeId = 0;
        } else {
            $scope   = $site->getIsWebsite() ? 'websites' : 'stores';
            $scopeId = $site->getChildId();
        }
        $config->saveConfig($unsecure, $url, $scope, $scopeId);
        $config->saveConfig($baseLinkUnsecure, $url, $scope, $scopeId);
        //$config->cleanCache();
    }

    /**
     * extra domain validations
     * @param string
     */
    public function checkExtraDomain($str)
    {
        $regex = "/^([a-z0-9][a-z0-9\-\.]{1,63})$/i";
        //Remove space after comma
        $nameStr = preg_replace('/\s*,\s*/', ',', $str);
        $split = explode(",", $nameStr);
        if (count($split) !== count(array_unique($split))) {
            $result['status'] = "error";
            $implodevals = array_diff_key($split, array_unique($split));
            $result['data'] = implode(',', $implodevals);
            $result['message'] = "duplicate domain names";
            return $result;
        }
        foreach ($split as $key => $value) {
            $valueTrim = rtrim($value, ',');
            if ((!preg_match($regex, $valueTrim)) && !empty($valueTrim)) {
                $result[] = $valueTrim;
            }
        }
        if (!empty($result)) {
            $data['status'] = "error";
            $data['data'] = implode(',', $result);
            $data['message'] = ' invalid characters, it must only contain alphanumerics, dashes "-" and full stops "." ';
            return $data;
        }

        return true;
    }

}
