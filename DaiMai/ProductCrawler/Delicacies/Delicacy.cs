using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace ProductCrawler.Delicacies
{
    class Delicacy : ICrawler
    {
        public string URL = "https://api.idelivery.com.tw/platform/storeinfo?id=";

        public List<string> DataList = new List<string>();

        private int _id = 0;

        public Delicacy(int id)
        {
            _id = id;
        }

        public async Task Get()
        {
            HttpClient httpClient = new HttpClient();

            httpClient.DefaultRequestHeaders.TryAddWithoutValidation("Authorization", "NzJhMDZiMzE0NWU0NDlkMGY0ZDMzYTJiMTE5OTYzMGQ0YmU1M2M1ZA==");

            for (var i = 0; i < _id; i++)
            {
                string url = this.URL + (i + 1).ToString();

                var responseMessage = await httpClient.GetAsync(url);

                if (responseMessage.IsSuccessStatusCode)
                {
                    var responseContent = await responseMessage.Content.ReadAsStringAsync();

                    var jsonObject = Newtonsoft.Json.Linq.JObject.Parse(responseContent);

                    if (jsonObject.ContainsKey("data"))
                    {
                        Console.WriteLine(jsonObject["data"]["company_name"].ToString() + ':' + (i + 1));
                        string data = jsonObject["data"].ToString();
                        DataList.Add(data);
                    }
                    else
                    {
                        Console.WriteLine("No Data.");
                    }
                }
            }
        }

        public void GenerateFile()
        {
            string combinedJson = "[" + string.Join(",", DataList) + "]";

            File.WriteAllText("Product.json", combinedJson);
        }
    }
}
