<?php

    use Sse\Data;
    use Sse\SSE;
    use Sse\Event;

    class ApiObjectSse extends ApiObject
    {
        public static $data;
        
        const MSECS_RESP = 250;         // min responsetime to wait
        const HEARTBEAT_GRACE = 15000; //heartbeart every msecs
        
        function send_msg($sseSess = null, $id = null, $msg = null) 
        {
            echo ":" . str_repeat(" ", 2048) . "\n"; // 2 kB padding for IE
            if($sseSess === null)
            {
                //Heartbeat.
                flush();
                ob_flush();
                return;
            }
            elseif($sseSess === false)
            {
                //STOP!
                echo "id: $id" . PHP_EOL;
                echo "data: ". json_encode(array('action' => 'stop')) . PHP_EOL;
                echo 'time: ' .  $sseSess->started();
                echo PHP_EOL;
                ob_flush();
                flush();
            }
            else
            {
                //Regular
                $data = array('message' => $msg, 'sent' => time() , 'time' => $sseSess->started() );
            }
            if($id === false)
            {
             //   $data['expired'] = 1;
            }
            echo 'retry: 250' . PHP_EOL; //1ms
            echo "id: $id" . PHP_EOL;
            echo "data: ". json_encode($data) . PHP_EOL;
            echo PHP_EOL;
            ob_flush();
            flush();
        }
        
        public function recv($data)
        {
            Db::in()->ssetest()->insert(array('message' => $data['message']));
            exit;
        }
        
        public function updates($data)
        {            
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

    // in the event of client reconnect, it will send Last-Event-ID in the headers
    // this only evaluated during the first request and subsequent reconnect from client
            $last_event_id = floatval(isset($_SERVER["HTTP_LAST_EVENT_ID"]) ? $_SERVER["HTTP_LAST_EVENT_ID"] : False);
            if ($last_event_id == 0)
            {
                $last_event_id = floatval(isset($_GET["lastEventId"]) ? $_GET["lastEventId"] : False);
            }
         //   isset($data['time']) || $data['time'] = null;
            
            $last_id = $last_event_id;
    // also keep our own last id for normal updates but favor last_event_id if it exists
    // since on each reconnect, this value will lost
            $sseSess = ModelSsesess::in();
            $sseSess->loadBy('sessid', session_id());
            $sseSess->sessid(session_id());
            $sseSess->started($data['time']);
            $sseSess->store();
            $limit = time() + 3600; //or infinite
            
            //Register this session...
            session_write_close(); //"detach"...... 
            
            $time = 0;
            $this->send_msg($sseSess, null, 'Welcome!');
            while (time() < $limit && $sseSess->loadBy(array('sessid' => session_id(),)))  //@started must be updated when the client 'reloads' the page, we must check if he still is there... after 15 minutes, say goodbye
            {
                if($sseSess && $sseSess->started() != $data['time'])
                {
             //       $this->send_msg(false);
                }
                if($last_id)
                {
                    foreach($this->_getMsgs($last_id) as $row)
                    {
                        $this->send_msg($sseSess, $row['id'], $row['message']);
                        $last_id = $row['id'];
                    }
                }
                else
                {
                   $last_id = $this->_getLastId();
                }
                usleep(static::MSECS_RESP*1000); //very fast... 0.25s response! (todo, memcache the result for this loop... data summing shit)
                $time += static::MSECS_RESP;
                if($time == static::HEARTBEAT_GRACE)
                {
                    $time = 0;
                   // $this->send_msg($sseSess, null, '--heartbeat--');
                    $this->send_msg(null);
                }
            }
        }
        
        protected function _getMsgs($last_id)
        {
            $cache = Cache::in();
            $ms_ = microtime(true);
            $ms__ = explode('.', $ms_);
            $ms = $ms__[1];
            $id = 'sse.id.' . date('Y-m-d H.i.s') . round($ms/static::MSECS_RESP); // each step.
            if(rand(1,10000) == 100)
            {
                $cache->deleteTags(array('sseidm'));
            }
            if(!$result = $cache->get($id))
            {
                $result = array();
                $resultset = Db::in()->ssetest()->select("id, message")->where('id > ?', $last_id);
                foreach($resultset as $msg)
                {
                    $result[] = iterator_to_array($msg);
                }
                $cache->set($id, $result, 2, array('sseidm'));
            }
            return $result;
        }
        
        protected function _getLastId()
        {
            $cache = Cache::in();
            $ms_ = microtime(true);
            $ms__ = explode('.', $ms_);
            $ms = $ms__[1];
            $id = 'sse.id.' . date('H.i.') . round($ms/static::MSECS_RESP); // each 250ms.
            if(rand(1,10000) == 100)
            {
                $cache->deleteTags(array('sseidm'));
            }
            if(!$last_id = $cache->get($id))
            {
                $last_id = Db::in()->ssetest()->select('MAX(id)-5 AS id')->fetch();
                $last_id = $last_id ? $last_id['id'] : null;
                $cache->set($id, $last_id, 2, array('sseid'));
            }
            return $last_id;
        }
    }
