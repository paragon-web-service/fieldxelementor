LIST_COUNTIES = ["Coronado San Diego Diego County California", 
" Del Mar San Diego Diego County California", 
" El Cajon San Diego Diego County California", 
" Encinitas San Diego Diego County California", 
" Escondido San Diego Diego County California", 
" Hillcrest San Diego Diego County California", 
" La Jolla San Diego Diego County California", 
" La Mesa San Diego Diego County California", 
" Lemon Grove San Diego Diego County California", 
" Mission Valley San Diego Diego County California", 
" National City San Diego Diego County California", 
" Newport Beach San Diego Diego County California", 
" Oceanside San Diego Diego County California", 
" Pacific Beach San Diego Diego County California", 
" Point Loma San Diego Diego County California", 
" Poway San Diego Diego County California", 
" Rancho Bernardo San Diego Diego County California", 
" Rancho Santa Fe San Diego Diego County California", 
" San Marcos San Diego Diego County California", 
" Santee San Diego Diego County California", 
" San Diego San Diego Diego County California", 
" Solana Beach San Diego Diego County California", 
" Spring Valley San Diego Diego County California", 
" Vista San Diego Diego County California", ]

LIST_LAWYER_TYPES = ['DUI Lawyer ', 
'DWUI Lawyer ', 
'Criminal Lawyer', 
'Assault & Battery lawyer', 
'Assault & Battery lawyer',  
'Homicide lawyer ', 
'Property Crimes lawyer ', 
'Elder Abuse lawyer ', 
'Elder Abuse lawyer ', 
'Elder Abuse lawyer', 
'Juvenile Crimes lawyer',  
'Juvenile Crimes lawyer',  
'Juvenile Crimes lawyer',  
'Restraining Order lawyer ', 
'Expungement lawyer',  
'Military lawyer ', 
'Kidnapping lawyer ', 
'Gun & Weapon Crimes lawyer', 
'Suspended License lawyer', 
]

List_answers =[]

for Lawyer in LIST_LAWYER_TYPES:
    for County in LIST_COUNTIES:
        List_answers.append(Lawyer + County)
for i in List_answers:
    print(i)