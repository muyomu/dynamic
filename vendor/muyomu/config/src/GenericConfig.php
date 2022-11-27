<?php

namespace muyomu\config;

use muyomu\config\client\UserClient;
use muyomu\config\utility\ConfigParser;

abstract class GenericConfig implements UserClient
{
    //解析器
    private ConfigParser $parser;

    protected string $configClass = '';

    //默认配置数组
    protected array $configData = array();

    public function __construct()
    {
        $this->parser = new ConfigParser();
    }

    /**
     * @param string $option
     * @return mixed
     */
    public function getOptions(string $option): mixed
    {
        if ($this->configClass == ''){
            return null;
        }

        $optionData = $this->parser->getConfigData($this->configClass);

        return $this->getOptionFieldData($option,$optionData);
    }

    /**
     * @param string $field
     * @param array $optionData
     * @return mixed
     */
    private function getOptionFieldData(string $field,array $optionData):mixed{

        $keys = explode(".",$field);

        foreach ($keys as $key){
            if (array_key_exists($key,$optionData)){
                if (is_array($optionData[$key])){
                    if ($this->parser->checkForAssocArray($optionData[$key])){
                        $keyM = array_shift($keys);
                        $optionData = $optionData[$keyM];
                        $key = array_shift($keys);
                        return $this->getOptionFieldData($key,$optionData);
                    }else{
                        return $optionData[$key];
                    }
                }else{
                    return $optionData[$key];
                }
            }else{
                return null;
            }
        }
        return null;
    }
}