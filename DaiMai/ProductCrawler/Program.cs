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
        static async Task Main(int args, string[] argv)
        {
            Console.WriteLine("請輸入產品總比數:");

            if (args != 2)
            {
                Console.WriteLine("Argment Error");
                return ;
            }

            int start = int.Parse(argv[0]);
            int end = int.Parse(argv[1]);

            if (end < start)
            {
                Console.WriteLine("End should be greater than start.");
                return ;
            }

            var delicacy = new Delicacy(start, end);

            await delicacy.Get();

            delicacy.GenerateFile();
        }
    }
}
