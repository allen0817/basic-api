from kafka import KafkaProducer
import sys

producer = KafkaProducer(bootstrap_servers='gditnm:9092')
producer.send('problem_send', bytes(sys.argv[1]))
producer.flush()