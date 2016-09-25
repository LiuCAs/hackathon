# hackaton
-
curl -XDELETE http://172.17.0.2:9200/ulice?pretty=true
curl -XPUT http://172.17.0.2:9200/ulice?pretty=true -d @tsconfig.json

curl -XPOST http://172.17.0.2:9200/teryt/ulice/_search?pretty -d '{
  "from" : 0,
  "size" : 1,
  "min_score": 0.005,
  "query": {
    "bool": {
      "must": [{
        "match": {
          "code": {
            "query": "3064",
            "operator": "and",
            "boost": 0.1
          }
        }
      }],
      "should": [{
        "match": {
          "name": {
            "query": "pobicia",
            "operator": "and"
          }
        }
      },
      {
        "match": {
          "name": {
            "query": "ulicy",
            "operator": "and"
          }
        }
      },
      {
        "match": {
          "name": {
            "query": "Czerwca",
            "operator": "and"
          }
        }
      }]
    }
  }
}'