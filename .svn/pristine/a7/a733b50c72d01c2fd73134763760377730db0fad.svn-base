<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

require_once(LIB_DIR . 'flexihash/flexihash.php');

class Lib_Redis
{
	private $redis  = null;
    private $hasher = null;
    private $poll   = array();

	public function __construct($servers)
	{
		$this->redis  = new Redis();
        $this->hasher = new Flexihash();

        foreach($servers as $server)
        {
        	$node = "{$server[0]}:{$server[1]}";
            $this->poll[$node] = FALSE;
            $nodes[] = $node;
        }

        $this->hasher->addTargets($nodes);    
	}
    
    private function connect($node)
    {
        if($this->poll[$node])
        {
            return TRUE;
        }

        $server = explode(':', $node);

        $this->poll[$node] = $this->redis->connect($server[0], $server[1]);

        return $this->poll[$node];
    }
    
	public function set($key, $value, $timeout=0)
	{
        $value = serialize($value);

        $nodes = $this->hasher->lookupList($key, count($this->poll)); 
        
        $flag = FALSE;

        foreach($nodes as $node)
        {
        	if($this->connect($node))
        	{
        		$res = $this->redis->set($key, $value);

        		if($res AND $timeout)
        		{
        			$this->redis->setTimeout($key, $timeout);
        		}

        		if(!$flag AND $res)
        		{
        			$flag = TRUE; //只要有一个节点设置成功就算成功
        		}
        	}
        }

        return $flag;      
	}

	public function get($key)
	{
		$nodes = $this->hasher->lookupList($key, count($this->poll));

		foreach($nodes as $node)
		{
		    if($this->connect($node))
		    {
		    	$value = $this->redis->get($key);

		    	if($value)
		    	{
		    		return unserialize($value);
		    	}
		    }	
		}

		return FALSE;
	}

    public function delete($key)
    {
        $keys = is_array($key) ? $key : array($key);
        
        foreach($keys as $item)
        {
            $nodes = $this->hasher->lookupList($item, count($this->poll));
            
            foreach($nodes as $node)
            {
                if($this->connect($node))
                {
                    $num = $this->redis->delete($item);
                }
            }
        }
    }

    public function increment($key, $step=1)
    {
        $nodes = $this->hasher->lookupList($key, count($this->poll));
        
        $value = 0;

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $newValue = $this->redis->incrBy($key, $step);

                if($newValue AND !$value)
                {
                    $value = $newValue;
                }
            }
        }

        return $value;
    }


    public function lPush($key, $value)
    {
        $value = serialize($value);

        $nodes = $this->hasher->lookupList($key, count($this->poll));
        
        $newValue = 0;

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $res = $this->redis->lpush($key, $value);

                if($res AND !$newValue)
                {
                    $newValue = $res;
                }
            }
        }

        return $newValue;
    }

    public function rPush($key, $value)
    {
        $value = serialize($value);

        $nodes = $this->hasher->lookupList($key, count($this->poll));

        $newValue = 0;

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $res = $this->redis->rpush($key, $value);

                if($res AND !$newValue)
                {
                    $newValue = $res;
                }
            }
        }

        return $newValue;
    }

    public function ltrim($key, $start, $stop)
    {
        $nodes = $this->hasher->lookupList($key, count($this->poll));
        $newValue = FALSE;
        $res = FALSE;

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $res = $this->redis->ltrim($key, $start, $stop);
            }

            if(!$newValue AND $res)
            {
                $newValue = $res;
            }
        }

        return $newValue;
    }

    public function lrange($key, $start, $stop)
    {
        $nodes = $this->hasher->lookupList($key, count($this->poll));
        
        $value = array();

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $v = $this->redis->lrange($key, $start, $stop);

                if($v)
                {
                    $value = $v;

                    break;
                }
            }   
        }

        foreach($value as $index=>$item)
        {
            $value[$index] = unserialize($item);
        }

        return $value;
    }

    public function hSet($key, $field, $value, $timeout=0)
    {
        $nodes = $this->hasher->lookupList($key, count($this->poll));

        $value = FALSE;

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $v = $this->redis->hSet($key, $field, $value);

                if($v !== FALSE AND $value===FALSE)
                {
                    $value = $v;
                }
            }
        }

        return $value;
    }

    public function hGet($key, $field)
    {
        $nodes = $this->hasher->lookupList($key, count($this->poll));

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $value = $this->redis->hGet($key, $field);

                if($value)
                {
                    return $value;
                }
            }   
        }

        return FALSE;
    }

    public function hGetAll($key)
    {
        $nodes = $this->hasher->lookupList($key, count($this->poll));

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $v = $this->redis->hGetAll($key);

                if($v)
                {
                    return $v;
                }
            }
        }

        return array();
    }

    public function hIncrBy($key, $field, $step=1)
    {
        $nodes = $this->hasher->lookupList($key, count($this->poll));
        
        $value= FALSE;

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $v = $this->redis->hIncrBy($key, $field, $step);

                if($value===FALSE)
                {
                    $value = $v;
                }
            }
        }

        return $value;
    }

    public function hMSet($key, $value)
    {
        $nodes = $this->hasher->lookupList($key, count($this->poll));
        
        $value = FALSE;

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $v = $this->redis->hMset($key, $value);

                if($value === FALSE)
                {
                    $value = $v;
                }
            }
        }

        return $value;
    }

    public function hMGet($key, $fields)
    {
        $nodes = $this->hasher->lookupList($key, count($this->poll));

        foreach($nodes as $node)
        {
            if($this->connect($node))
            {
                $v = $this->redis->hMGet($key, $fields);

                if($v)
                {
                    return $v;
                }
            }
        }

        return array();
    }

    



}