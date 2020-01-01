from IPython.display import display, clear_output, display_html
from urllib.request import urlopen
import pandas as pd
import datetime
import requests
import sched
import time
import json
import numpy as np
import matplotlib.pyplot as plt
import pymysql.cursors
from talib import abstract
from sqlalchemy import create_engine
from io import StringIO

#mysql+pymysql：engine = create_engine("mysql+pymysql://username:password@hostname:port/dbname",echo=True)
#mssql+pymssql: engine = create_engine('mssql+pymssql://username:password@hostname:port/dbname',echo=True)

mysql = "" #輸入database 

def chunks(l, n):
    # For item i in a range that is a length of l,
    for i in range(0, len(l), n):
        # Create an index range for l of n items:
        yield l[i:i+n]

def crawl_legal_person(date):
    
    # 將時間物件變成字串：'20180102'
    datestr = date.strftime('%Y%m%d')
    #datestr = date
    #print (datestr)
    # 下載三大法人資料
    try:
        r = requests.get('http://www.tse.com.tw/fund/T86?response=csv&date='+datestr+'&selectType=ALLBUT0999')#
    except:
        return None

    # 製作三大法人的DataFrame
    try:
        df = pd.read_csv(StringIO(r.text), header=1).dropna(how='all', axis=1).dropna(how='any')
        
        #ret = df.set_index('證券名稱')
    except:
        return None
    
    # 微調整（為了配合資料庫的格式）

    # 刪除逗點
    df = df.astype(str).apply(lambda s: s.str.replace(',',''))
    
    # 刪除「證券代號」中的「"」和「=」
    df['證券代號'] = df['證券代號'].str.replace('=','').str.replace('"','')
    
    
    # 設定index
    df['date'] = datestr
    
    return df


def crawl_price(date):
    datestr = date.strftime('%Y%m%d')
    try:
        r = requests.post('http://www.twse.com.tw/exchangeReport/MI_INDEX?response=csv&date=' + str(date).split(' ')[0].replace('-','') + '&type=ALL')
    except:
        return None
    ret = pd.read_csv(StringIO("\n".join([i.translate({ord(c): None for c in ' '}) for i in r.text.split('\n') if len(i.split('",')) == 17 and i[0] != '='])), header=0)
    
    ret['成交金額'] = ret['成交金額'].str.replace(',','')
    ret['成交股數'] = ret['成交股數'].str.replace(',','')
    ret['date'] = datestr
    return ret
    


def crawl_eps():
    pop = pd.DataFrame(columns=["代碼","名稱","營業收入","營業損益","業外收入","稅前損益","稅後損益","每股EPS(元)"])
    source = requests.get('https://www.cnyes.com/twstock/financial4.aspx') 
    soup = BeautifulSoup(source.text, "lxml")
    datas = table.find_all("td")
    row_data = [data.text for data in datas]
    rows_data = list(chunks(row_data, 8))
    for ele in rows_data:
        temp_df = pd.DataFrame([ele], columns=["代碼","名稱","營業收入","營業損益","業外收入","稅前損益","稅後損益","每股EPS(元)"])
        pop = pop.append(temp_df).reset_index(drop=True)

    #print (pop)
    return pop

def main(delay = 10 , n_days = 9,allow_continuous_fail_count = 5):
    
    day = 0
    date = datetime.datetime.now()
    
    print ("Today is ",date)
    engine = create_engine(mysql)
    fail_count = 0
    legal_person_check = 0
    price_check = 0
    legal_person_sql = "SELECT DISTINCT date from legalperson ORDER BY date DESC"
    legal_person_check_date = pd.read_sql_query(legal_person_sql, engine)
    price_sql = "SELECT DISTINCT date from price ORDER BY date DESC"
    price_check_date = pd.read_sql_query(price_sql, engine)


    while day < n_days:

        print('parsing', date)
        # 使用 crawPrice 爬資料
        try:
            # 抓資料
            print ('get legal_person')
            
            # 判斷資料庫有無重複資料
            if (engine.has_table("legalperson")):
                legal_person_sql = "select date from legalperson where date='"+date.strftime('%Y%m%d')+"'"
                legal_person_check = pd.read_sql_query(legal_person_sql, engine)
                if len(legal_person_check)==0:
                    legal_person_check = 0
                else:
                    legal_person_check = 1
            else:
                legal_person_check = 0
            
            if legal_person_check==0:
                crawl_legal_person_data = crawl_legal_person(date)
                if crawl_legal_person_data is not None:
                    print (crawl_legal_person_data)
                    crawl_legal_person_data.to_sql('legalperson', engine, if_exists='append', index= False)
                else:
                    print ("None Data Maybe the date is holiday")
                
                
            else:
                print('This day legal_person already have!')
                #pass
            print ('get price')
            
            if (engine.has_table("price")):
                price_sql = "select date from price where date='"+date.strftime('%Y%m%d')+"'"
                price_check = pd.read_sql_query(price_sql, engine)
                if len(price_check)==0:
                    price_check = 0
                else:
                    price_check = 1
            else:
                price_check=0
            
            if price_check==0:
                crawl_price_data=crawl_price(date)
                if crawl_price_data is not None:
                    print (crawl_price_data)
                    crawl_price_data.to_sql('price', engine, if_exists='append', index= False)
                else:
                    print ("None Data Maybe the date is holiday")
                
                
            else:
                print('This day price already have!')
                #pass
                
            print('success!')
            fail_count = 0
            day +=1
        except:
            # 假日爬不到
            print('fail! check the date is holiday')
            fail_count += 1
            if fail_count == allow_continuous_fail_count:
                raise
                break
        
        # 減一天
        date -= datetime.timedelta(days=1)
        print('wait')
        time.sleep(delay)
    

    
        
if __name__ == '__main__':
    #main(delay,day)
    main(10,180)
    