using ProductCrawler.Delicacies;
using System;
using System.Collections.Generic;
using System.IO;
using System.Net.Http;
using System.Threading.Tasks;

namespace ProductCrawler
{
    class Program
    {
        static async Task Main(string[] args)
        {
            Console.WriteLine("請輸入產品總比數:");

            int id = int.Parse(Console.ReadLine());

            Console.WriteLine("您輸入的是：" + id);

            var delicacy = new Delicacy(id);

            await delicacy.Get();

            delicacy.GenerateFile();
        }
    }
}
